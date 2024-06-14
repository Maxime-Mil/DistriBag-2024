#include <iostream>
#include <bitset>
#include <wiringPi.h>
#include <wiringSerial.h>
#include <unistd.h>
#include <termios.h>
#include <cstring>


using namespace std;

const int MAX_FRAME_SIZE = 1; // Maximum size of the frame (in bytes)
 


void readFrame(int serialPort) {


    while (true) {
        // Check if there's data available in the serial buffer
        int nbBytes = serialDataAvail(serialPort);
        if (nbBytes  > 0) {
            // Read the incoming byte
            unsigned char byte = serialGetchar(serialPort);
                
                // Print the received byte data
                cout << "Nb Byte : " << nbBytes << endl;
            
                cout << "Received frame: Data: " << bitset<8>{byte} << endl;
            //}
        }

        // Wait for a short duration before checking for more data
        //delay(10); // Adjust as needed
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



    struct termios options ;
    memset(&options, 0, sizeof(options));

    if (tcgetattr(serialPort, &options) != 0) {
        cerr << "Error getting current serial port options" << endl;
        close(serialPort);
        return -1;
    }


    // Set 8 data bits
    options.c_cflag &= ~CSIZE;
    options.c_cflag |= CS8;

    // Set 1 stop bit
    options.c_cflag &= ~CSTOPB;

    // Enable parity and set even parity
    options.c_cflag |= PARENB;
    options.c_cflag &= ~PARODD;
    

    tcsetattr (serialPort, TCSANOW, &options) ;   // Activer les nouvelles options
    
    try {
        readFrame(serialPort);
    } catch (const std::exception &e) {
        cerr << "Exception: " << e.what() << endl;
    }

    // Close the serial port
    serialClose(serialPort);
    return 0;
}
