# Installation

#. 1. Download Kafka
# wget https://dlcdn.apache.org/kafka/3.9.0/kafka_2.13-3.9.0.tgz

#. 2. Extract
# tar -xvf kafka_2.13-3.9.0.tgz 

#. 3. Move
# mv kafka_2.13-3.9.0 /opt/kafka/

#. 4. Create config/zookeeper.properties
# nano /opt/kafka/config/zookeeper.properties

#. 5. Create config/server.properties
# nano /opt/kafka/config/server.properties


#!/bin/bash

cd /opt/kafka/

# start zookeeper
bin/zookeeper-server-start.sh config/zookeeper.properties

# start kafka
bin/kafka-server-start.sh config/server.properties

# create topic
bin/kafka-topics.sh --create --topic testsendbulksms --bootstrap-server localhost:9092 --partitions 1 --replication-factor 1

# list topics
bin/kafka-topics.sh --list --bootstrap-server localhost:9092

# produce data to topic
bin/kafka-console-producer.sh --topic testsendbulksms --bootstrap-server localhost:9092

# consume data from topic
bin/kafka-console-consumer.sh --topic testsendbulksms --from-beginning --bootstrap-server localhost:9092

# To see only new messages arriving in the topic
bin/kafka-console-consumer.sh --topic testsendbulksms --bootstrap-server localhost:9092

# Verify partitions
bin/kafka-topics.sh  --topic testsendbulksms --bootstrap-server localhost:9092 --describe

# delete top
bin/kafka-topics.sh --bootstrap-server localhost:9092 --delete --topic testsendbulksms
bin/kafka-topics.sh --bootstrap-server localhost:9092 --delete --topic scheduledsms
bin/kafka-topics.sh --bootstrap-server localhost:9092 --delete --topic __consumer_offsets


# Installation and configuration instructions
# 1. Create kafka user
sudo useradd kafka -m
sudo passwd kafka
sudo chown -R kafka:kafka /opt/kafka

# 2. Manually start zookeeper and kafka
/opt/kafka/bin/zookeeper-server-start.sh /opt/kafka/config/zookeeper.properties
/opt/kafka/bin/kafka-server-start.sh /opt/kafka/config/server.properties

# 3. Configure zookeeper
sudo nano /etc/systemd/system/zookeeper.service
# Content start

# [Unit]
# Description=Apache Zookeeper
# After=network.target

# [Service]
# User=kafka
# Group=kafka
# ExecStart=/opt/kafka/bin/zookeeper-server-start.sh /opt/kafka/config/zookeeper.properties
# ExecStop=/opt/kafka/bin/zookeeper-server-stop.sh
# Restart=on-abnormal

# [Install]
# WantedBy=multi-user.target

# Content end

# 4. Configure kafka
sudo nano /etc/systemd/system/kafka.service
# Content start

# [Unit]
# Description=Apache Kafka
# After=zookeeper.service

# [Service]
# User=kafka
# Group=kafka
# ExecStart=/opt/kafka/bin/kafka-server-start.sh /opt/kafka/config/server.properties
# ExecStop=/opt/kafka/bin/kafka-server-stop.sh
# Restart=on-abnormal

# [Install]
# WantedBy=multi-user.target

# Content end

# 5. Reload systemd daemon
sudo systemctl daemon-reload
sudo systemctl enable zookeeper
sudo systemctl enable kafka

# 6. Start services
sudo systemctl start zookeeper
sudo systemctl start kafka

# 7. Check status
sudo systemctl status zookeeper
sudo systemctl status kafka

# Manually
# Create a topic
/opt/kafka/bin/kafka-topics.sh --create --topic testsendbulksms --bootstrap-server localhost:9092 --partitions 1 --replication-factor 1

# Produce a message
/opt/kafka/bin/kafka-console-producer.sh --topic testsendbulksms --bootstrap-server localhost:9092

# Consume messages
/opt/kafka/bin/kafka-console-consumer.sh --topic testsendbulksms --from-beginning --bootstrap-server localhost:9092
