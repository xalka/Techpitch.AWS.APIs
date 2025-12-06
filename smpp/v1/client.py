import socket

def send_data(data):
    # Define the server address and port
    server_address = ('127.0.0.1', 3000)

    # Create a socket object
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        # Connect to the server
        s.connect(server_address)

        # Send data to the server
        s.sendall(data.encode())

        # Receive response from the server
        response = s.recv(1024)
        print('Received:', response.decode())

# Test the function
data_to_send = 'Hello, server!'
send_data(data_to_send)