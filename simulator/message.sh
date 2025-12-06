curl -X POST http://127.0.0.1:2501/message/v1/create \
-H "groupId: 1" \
-H "adminId: 1" \
-H "alphanumeric: TP" \
-d '{
    "to" : [ 715003415, 254715003416, 254715003417, 254715003418, 254715003419, 254715003420, 254715003421, 254715003422, 254715003423, 254715003424 ],
    "message" : "Hey! Hope you are doing well. Just wanted to remind you about our meeting tomorrow at 2 PM. Let me know if you need to reschedule. Also, feel free to bring any questions you have - I will be happy to go over everything with you. Looking forward to it! See you soon",
    "alphanumeric" : "TP",
    "scheduled" : null
}'