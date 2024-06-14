import serial
import time

# Open the serial port
ser = serial.Serial(
    port='/dev/ttyS0',  # Replace with your UART port
    baudrate=9600,
    bytesize=serial.EIGHTBITS,
    parity=serial.PARITY_NONE,
    stopbits=serial.STOPBITS_ONE,
    timeout=1
)

def invert_byte(byte):
    return ~byte & 0xFF

def check_odd_parity(data_bits, parity_bit):
    # Calculate the parity
    num_of_ones = bin(data_bits).count('1')
    calculated_parity = num_of_ones % 2  # 0 for even, 1 for odd
    # Parity bit should make the number of 1's odd
    return calculated_parity == parity_bit

def read_inverted_frame():
    while True:
        byte = ser.read(1)
        if byte:
            byte_value = int.from_bytes(byte, byteorder='big', signed=False)
            inverted_byte_value = invert_byte(byte_value)

            start_bit = (inverted_byte_value >> 9) & 0x01
            data_bits = (inverted_byte_value >> 1) & 0xFF
            parity_bit = (inverted_byte_value >> 8) & 0x01

            if start_bit == 1:
                if check_odd_parity(data_bits, parity_bit):
                    print(f"Received frame: Data: {bin(data_bits)}, Parity: {parity_bit}")
                else:
                    print(f"Invalid parity: Data: {bin(data_bits)}, Parity: {parity_bit}")
            else:
                print(f"Invalid frame: Start bit not 1")
        else:
            print("No data received")
        time.sleep(0.1)

try:
    read_inverted_frame()
except KeyboardInterrupt:
    ser.close()
    print("Serial port closed")
