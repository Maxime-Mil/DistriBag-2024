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

def invert_logic(byte):
    return ~byte & 0xFF

def check_odd_parity(data_bits, parity_bit):
    num_of_ones = bin(data_bits).count('1')
    # Odd parity check: number of 1's in data_bits plus parity_bit should be odd
    return (num_of_ones + parity_bit) % 2 == 1

def read_inverted_frame():
    while True:
        # Read two bytes to ensure we capture the whole frame
        bytes_read = ser.read(2)
        if len(bytes_read) < 2:
            print("Not enough data received")
            continue
        
        # Combine the bytes to form a 16-bit integer
        frame = int.from_bytes(bytes_read, byteorder='big', signed=False)
        
        # Invert the logic for the 16-bit frame
        inverted_frame = ~frame & 0xFFFF
        
        # Extract the 10-bit frame from the 16-bit value
        aligned_frame = (inverted_frame >> 6) & 0x3FF
        
        # Extract the components
        start_bit = (aligned_frame >> 9) & 0x01
        data_bits = (aligned_frame >> 1) & 0xFF
        parity_bit = aligned_frame & 0x01
        
        # Validate the start bit
        if start_bit == 1:
            # Validate the parity bit
            if check_odd_parity(data_bits, parity_bit):
                print(f"Received frame: Data: {bin(data_bits)}, Parity: {parity_bit}")
            else:
                print(f"Invalid parity: Data: {bin(data_bits)}, Parity: {parity_bit}")
        else:
            print(f"Invalid frame: Start bit not 1, frame bits: {bin(aligned_frame)}")
        
        time.sleep(0.1)

try:
    read_inverted_frame()
except KeyboardInterrupt:
    ser.close()
    print("Serial port closed")
