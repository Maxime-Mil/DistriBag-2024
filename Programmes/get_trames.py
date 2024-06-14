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

def read_inverted_frame():
    while True:
        # Read one byte
        byte = ser.read(1)
        if byte:
            # Convert byte to integer
            byte_value = int.from_bytes(byte, byteorder='big', signed=False)
            
            # Invert the logic: 5V (1) -> 0, 0V (0) -> 1
            inverted_value = ~byte_value & 0xFF
            
            # Extract the frame bits (assuming the 10-bit frame is aligned in one byte)
            frame_bits = inverted_value & 0x3FF
            
            # Verify the start bit is 1 and the stop bit is 0
            start_bit = (frame_bits >> 9) & 0x01
            stop_bit = frame_bits & 0x01
            
            if start_bit == 1 and stop_bit == 0:
                # Extract the data bits
                data_bits = (frame_bits >> 1) & 0xFF
                
                print(f"Received frame: {bin(frame_bits)}, Data: {bin(data_bits)}")
            else:
                print(f"Invalid frame: {bin(frame_bits)}")
        else:
            print("No data received")
        time.sleep(0.1)

try:
    read_inverted_frame()
except KeyboardInterrupt:
    ser.close()
    print("Serial port closed")
