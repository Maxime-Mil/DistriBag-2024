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

def invert_logic(value):
    return ~value & 0xFF

def check_odd_parity(data_bits, parity_bit):
    num_of_ones = bin(data_bits).count('1')
    return (num_of_ones % 2) != parity_bit

def read_inverted_frame():
    while True:
        # Read two bytes to ensure we capture all bits
        bytes_read = ser.read(2)
        if len(bytes_read) < 2:
            #print("Not enough data received")
            continue
        
        # Combine the bytes to form a 16-bit integer
        frame = int.from_bytes(bytes_read, byteorder='big', signed=False)

        # Invert the logic for the 16-bit frame
        inverted_frame = ~frame & 0xFFFF

        # We need to extract the 10-bit frame from this 16-bit value
        # Align the frame to the rightmost 10 bits
        frame_bits = (inverted_frame >> 6) & 0x03FF
        
        # Extract individual components
        start_bit = (frame_bits >> 9) & 0x01
        data_bits = (frame_bits >> 1) & 0xFF
        parity_bit = (frame_bits >> 0) & 0x01
        
        # Validate the start bit
        if start_bit == 1:
            # Validate the parity bit
            if check_odd_parity(data_bits, parity_bit):
                print(f"Received frame: Data: {bin(data_bits)}, Parity: {parity_bit}")
            else:
                print(f"Invalid parity: Data: {bin(data_bits)}, Parity: {parity_bit}")
        else:
            print(f"Invalid frame: Start bit not 1, frame bits: {bin(frame_bits)}")
        
        time.sleep(0.1)

try:
    read_inverted_frame()
except KeyboardInterrupt:
    ser.close()
    print("Serial port closed")
