#include <iostream>
#include <bitset>
#include <libserialport.h>
#include <cstring>

using namespace std;

const int MAX_FRAME_SIZE = 1; // Maximum size of the frame (in bytes)

void configureSerialPort(struct sp_port* port) {
    // Set baud rate
    if (sp_set_baudrate(port, 9600) != SP_OK) {
        cerr << "Error setting baud rate" << endl;
        sp_close(port);
        sp_free_port(port);
        exit(EXIT_FAILURE);
    }

    // Set 8 data bits
    if (sp_set_bits(port, 8) != SP_OK) {
        cerr << "Error setting data bits" << endl;
        sp_close(port);
        sp_free_port(port);
        exit(EXIT_FAILURE);
    }

    // Set 1 stop bit
    if (sp_set_stopbits(port, 1) != SP_OK) {
        cerr << "Error setting stop bits" << endl;
        sp_close(port);
        sp_free_port(port);
        exit(EXIT_FAILURE);
    }

    // Set even parity
    if (sp_set_parity(port, SP_PARITY_EVEN) != SP_OK) {
        cerr << "Error setting parity" << endl;
        sp_close(port);
        sp_free_port(port);
        exit(EXIT_FAILURE);
    }

    // Set flow control to none
    if (sp_set_flowcontrol(port, SP_FLOWCONTROL_NONE) != SP_OK) {
        cerr << "Error setting flow control" << endl;
        sp_close(port);
        sp_free_port(port);
        exit(EXIT_FAILURE);
    }
}

void readFrame(struct sp_port* port) {
    while (true) {
        // Buffer to store the incoming byte
        unsigned char byte;
        int bytesRead = sp_nonblocking_read(port, &byte, 1);

        if (bytesRead > 0) {
            // Print the received byte data
            cout << "Nb Bytes: " << bytesRead << endl;
            cout << "Received frame: Data: " << bitset<8>{byte} << endl;
        } else if (bytesRead < 0) {
            cerr << "Error reading from serial port" << endl;
        }
    }
}

int main() {
    struct sp_port *port;
    
    // Open the serial port
    if (sp_get_port_by_name("/dev/ttyS0", &port) != SP_OK) {
        cerr << "Unable to find serial device" << endl;
        return 1;
    }

    if (sp_open(port, SP_MODE_READ_WRITE) != SP_OK) {
        cerr << "Unable to open serial device" << endl;
        sp_free_port(port);
        return 1;
    }

    // Configure the serial port with the correct settings
    configureSerialPort(port);

    try {
        readFrame(port);
    } catch (const std::exception &e) {
        cerr << "Exception: " << e.what() << endl;
    }

    // Close the serial port
    sp_close(port);
    sp_free_port(port);
    return 0;
}
