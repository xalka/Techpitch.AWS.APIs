#!/bin/bash

curl -X POST \
--header "Content-Type: application/json" \
--header "Accept: application/json" \
--cacert /../cert/ca.crt \
--cert /../cert/client.crt \
--key /../cert/client.key \
-d '{"username": "TechPitchAPI", "password": "Admin@123"}' \
"https://dtsvc.safaricom.com:8480/api/auth/login"


# curl -X POST \
# --header "Content-Type: application/json" \
# --header "Accept: application/json" \
# --cacert /etc/letsencrypt/live/techxal.co.ke/fullchain.pem \
# --cert /etc/letsencrypt/live/techxal.co.ke/cert.pem \
# --key /etc/letsencrypt/live/techxal.co.ke/privkey.pem \
# -d '{"username": "TechPitchAPI", "password": "Admin@123"}' \
# "https://dtsvc.safaricom.com:8480/api/auth/login"


# curl -X POST \
# --header "Content-Type: application/json" \
# --header "Accept: application/json" \
# --header "X-Requested-With: XMLHttpRequest" \
# --cacert /etc/letsencrypt/live/techxal.co.ke/cert.pem \
# --cert /etc/letsencrypt/live/techxal.co.ke//client.crt \
# --key /etc/letsencrypt/live/techxal.co.ke//client.key \
# -d '{
#   "password": "Admin@123",
#   "username": "TechPitchAPI"
# }' \
# "https://dtsvc.safaricom.com:8480/api/auth/login"
