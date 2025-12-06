# pip install smpplib

import smpplib.client
import smpplib.consts
import time

SMPP_HOST = "localhost"
SMPP_PORT = 2775
TOKEN = "my_secure_token"  # Replace with your actual token

client = smpplib.client.Client(SMPP_HOST, SMPP_PORT)

client.connect()
client.bind_transceiver(system_id=TOKEN, password="")

while True:
    message = input("Enter message to send: ")
    client.send_message(
        source_addr_ton=smpplib.consts.SMPP_TON_ALNUM,
        source_addr="Sender",
        dest_addr_ton=smpplib.consts.SMPP_TON_INTERNATIONAL,
        destination_addr="254712345678",
        short_message=message.encode(),
    )
    time.sleep(2)
