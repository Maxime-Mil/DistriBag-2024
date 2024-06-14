#include <iostream>
#include <wiringPi.h>
#include <wiringSerial.h>
#include <unistd.h>
#include <bitset>

using namespace std;

int invertLogic(int byte) {
    return ~byte & 0xFF;
}

bool checkOddParity(int dataBits, int parityBit) {
    int numOfOnes = __builtin_popcount(dataBits);
    return (numOfOnes % 2) != parityBit;
}

void readInvertedFrame(int serialPort) {
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

        // Invert the logic for the 16-bit frame
        int invertedFrame = ~frame & 0xFFFF;

        // Extract the 10-bit frame from the 16-bit value
        int alignedFrame = (invertedFrame >> 6) & 0x03FF;

        // Extract the components
        int startBit = (alignedFrame >> 9) & 0x01;
        int dataBits = (alignedFrame >> 1) & 0xFF;
        int parityBit = alignedFrame & 0x01;

        // Validate the start bit
        if (startBit == 1) {
            // Validate the parity bit
            if (checkOddParity(dataBits, parityBit)) {
                cout << "Received frame: Data: " << bitset<8>(dataBits) << ", Parity: " << parityBit << endl;
            } else {
                cout << "Invalid parity: Data: " << bitset<8>(dataBits) << ", Parity: " << parityBit << endl;
            }
        } else {
            cout << "Invalid frame: Start bit not 1, frame bits: " << bitset<10>(alignedFrame) << endl;
        }

        usleep(100000); // Sleep for 0.1 seconds
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
        readInvertedFrame(serialPort);
    } catch (const std::exception &e) {
        cerr << "Exception: " << e.what() << endl;
    }

    // Close the serial port
    serialClose(serialPort);
    return 0;
}
