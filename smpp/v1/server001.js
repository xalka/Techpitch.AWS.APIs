// npm init -y
// npm install smpp kafkajs dotenv

// PORT=2775
// KAFKA_BROKER=localhost:9092
// TOPIC=sms_messages


require("dotenv").config();
const smpp = require("smpp");
const { Kafka } = require("kafkajs");

const PORT = process.env.PORT || 2775;
const KAFKA_BROKER = process.env.KAFKA_BROKER;
const TOPIC = process.env.TOPIC;

const kafka = new Kafka({ brokers: [KAFKA_BROKER] });
const producer = kafka.producer();
let activeConnections = new Map(); // Stores token-user connections

async function startKafka() {
  await producer.connect();
  console.log("Connected to Kafka");
}

const server = smpp.createServer((session) => {
  let clientToken = null;

  session.on("bind_transceiver", async (pdu) => {
    clientToken = pdu.system_id; // Using system_id as the token

    if (!clientToken) {
      return session.send(
        new smpp.PDU("bind_transceiver_resp", { command_status: smpp.ESME_RINVPASWD })
      );
    }

    if (activeConnections.has(clientToken)) {
      return session.send(
        new smpp.PDU("bind_transceiver_resp", { command_status: smpp.ESME_RALYBND })
      );
    }

    activeConnections.set(clientToken, session);
    console.log(`Client connected with token: ${clientToken}`);

    session.send(new smpp.PDU("bind_transceiver_resp", { command_status: 0 }));
  });

  session.on("submit_sm", async (pdu) => {
    if (!clientToken || !activeConnections.has(clientToken)) {
      return session.send(new smpp.PDU("submit_sm_resp", { command_status: smpp.ESME_RINVCMDID }));
    }

    console.log(`Received message from ${clientToken}: ${pdu.short_message.message}`);

    await producer.send({
      topic: TOPIC,
      messages: [{ key: clientToken, value: pdu.short_message.message }],
    });

    session.send(new smpp.PDU("submit_sm_resp", { command_status: 0, message_id: "12345" }));
  });

  session.on("unbind", () => {
    if (clientToken) {
      activeConnections.delete(clientToken);
      console.log(`Client disconnected: ${clientToken}`);
    }
    session.send(new smpp.PDU("unbind_resp"));
    session.close();
  });

  session.on("close", () => {
    if (clientToken) {
      activeConnections.delete(clientToken);
      console.log(`Client connection closed: ${clientToken}`);
    }
  });
});

server.listen(PORT, async () => {
  console.log(`SMPP server running on port ${PORT}`);
  await startKafka();
});
