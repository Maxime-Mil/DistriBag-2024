#include <iostream>
#include <bitset>
#include <libserialport.h>
#include <cstring>
#include <unordered_map>

using namespace std;

const int MAX_FRAME_SIZE = 1; // Maximum size of the frame (in bytes)

// Map to store coin values and their corresponding increments
unordered_map<int, double> coinMap = {
    {40, 2.0},
    {20, 1.0},
    {10, 0.5},
    {4, 0.2},
    {2, 0.1},
    {1, 0.05}
};

void checkAndHandleError(enum sp_return result, const char* message) {
    if (result != SP_OK) {
        cerr << message << ": " << sp_last_error_message() << endl;
        sp_free_error_message(sp_last_error_message());
        exit(EXIT_FAILURE);
    }
}

void configureSerialPort(struct sp_port* port) {
    enum sp_return result;

    // Set baud rate
    result = sp_set_baudrate(port, 9600);
    checkAndHandleError(result, "Error setting baud rate");

    // Set 8 data bits
    result = sp_set_bits(port, 8);
    checkAndHandleError(result, "Error setting data bits");

    // Set 1 stop bit
    result = sp_set_stopbits(port, 1);
    checkAndHandleError(result, "Error setting stop bits");

    // Set even parity
    /* result = sp_set_parity(port, SP_PARITY_EVEN);
    checkAndHandleError(result, "Error setting parity"); */

    // Set flow control to none
    result = sp_set_flowcontrol(port, SP_FLOWCONTROL_NONE);
    checkAndHandleError(result, "Error setting flow control");
}

void readFrame(struct sp_port* port, double &totalAmount) {
    while (true) {
        // Buffer to store the incoming byte
        unsigned char byte;
        int bytesRead = sp_blocking_read(port, &byte, 1, 1000); // 1 second timeout

        if (bytesRead > 0) {
            // Check if the received byte consists only of ones
            if (byte == 0xFF) {
                // Skip printing this byte
                continue;
            }

            // Print the received byte data
            cout << "Nb Bytes: " << bytesRead << endl;
            cout << "Received frame: Data: " << bitset<8>{byte} << " (" << static_cast<int>(byte) << ")" << endl;

            // Check if the byte corresponds to any value in the map
            auto it = coinMap.find(static_cast<int>(byte));
            if (it != coinMap.end()) {
                totalAmount += it->second;
                cout << "Coin inserted: " << it->first << ", Total amount: " << totalAmount << endl;
            } else {
                cout << "Unrecognized coin: " << static_cast<int>(byte) << endl;
            }
        } else if (bytesRead < 0) {
            cerr << "Error reading from serial port: " << sp_last_error_message() << endl;
            sp_free_error_message(sp_last_error_message());
        } else {
            continue;
        }
    }
}

int main() {
    struct sp_port *port;
    double totalAmount = 0.0;

    // List all available serial ports
    struct sp_port **ports;
    sp_list_ports(&ports);

    for (int i = 0; ports[i]; i++) {
        cout << "Found port: " << sp_get_port_name(ports[i]) << endl;
    }

    // Free the port list
    sp_free_port_list(ports);

    // Open the serial port
    if (sp_get_port_by_name("/dev/ttyS0", &port) != SP_OK) {
        cerr << "Unable to find serial device: " << sp_last_error_message() << endl;
        sp_free_error_message(sp_last_error_message());
        return 1;
    }

    if (sp_open(port, SP_MODE_READ_WRITE) != SP_OK) {
        cerr << "Unable to open serial device: " << sp_last_error_message() << endl;
        sp_free_error_message(sp_last_error_message());
        sp_free_port(port);
        return 1;
    }

    // Configure the serial port with the correct settings
    configureSerialPort(port);

    try {
        readFrame(port, totalAmount);
    } catch (const std::exception &e) {
        cerr << "Exception: " << e.what() << endl;
    }

    // Close the serial port
    sp_close(port);
    sp_free_port(port);
    return 0;
}
