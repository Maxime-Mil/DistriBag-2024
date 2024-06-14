#include <iostream>
#include <bitset>
#include <wiringPi.h>
#include <wiringSerial.h>
#include <unistd.h>

using namespace std;

void readFrame(int serialPort) {
    while (true) {
       if (serialDataAvail(serialPort) < 2) {
            usleep(100000); // Sleep for 0.1 seconds
            continue;
        }

        // Read two bytes to ensure we capture the whole frame
        int byte1 = serialGetchar(serialPort);
        int byte2 = serialGetchar(serialPort);

        // Combine the bytes to form a 16-bit integer
        int frame = (byte1 << 8) | byte2;

        // Extract the 8-bit data from the frame
        int dataBits = frame & 0xFF;

        // Print the received frame data (excluding parity bit)
        cout << "Received frame: Data: " << bitset<8>(dataBits) << endl;

        //usleep(100000); // Sleep for 0.1 seconds
    }
}

int main() {
    // Setup wiringPi
    if (wiringPiSetup() == -1) {
        cerr << "WiringPi setup failed" << endl;
        return 1;
    }

    // Open the serial port
    int serialPort = serialOpen("/dev/ttyS0", 9600);
    if (serialPort == -1) {
        cerr << "Unable to open serial device" << endl;
        return 1;
    }

    try {
        readFrame(serialPort);
    } catch (const std::exception &e) {
        cerr << "Exception: " << e.what() << endl;
    }

    // Close the serial port
    serialClose(serialPort);
    return 0;
}
