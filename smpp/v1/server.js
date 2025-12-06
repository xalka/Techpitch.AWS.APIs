const net = require('net');

// Create a TCP server
const server = net.createServer(socket => {
    console.log('Client connected');

    // Handle incoming data from the client
    socket.on('data', data => {
        console.log('Data received from client:', data.toString());
    });

    // Handle client connection termination
    socket.on('end', () => {
        console.log('Client disconnected');
    });

    // Handle errors
    socket.on('error', err => {
        console.error('Socket error:', err.message);
    });
});

// ensure the port is free
// sudo kill -9 $(lsof -ti :3000)

// Start the server and listen on a specific port
const PORT = 3000;
server.listen(PORT,() => {
    console.log(`Server started and listening on port ${PORT}`);
});