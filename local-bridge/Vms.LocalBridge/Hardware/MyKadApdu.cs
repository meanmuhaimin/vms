namespace Vms.LocalBridge.Hardware;

internal static class MyKadApdu
{
    private static readonly byte[] MyKadAid = [0xA0, 0x00, 0x00, 0x00, 0x74, 0x4A, 0x4D, 0x41, 0x53, 0x20, 0x49, 0x44];

    public static byte[] SelectMyKadApplication()
    {
        return [0x00, 0xA4, 0x04, 0x00, (byte)MyKadAid.Length, .. MyKadAid];
    }

    public static byte[] ReadBinary(ushort offset, byte length)
    {
        return [0x00, 0xB0, (byte)(offset >> 8), (byte)(offset & 0xFF), length];
    }

    public static bool IsSuccess(byte[] response)
    {
        return response.Length >= 2 && response[^2] == 0x90 && response[^1] == 0x00;
    }

    public static byte[] Payload(byte[] response)
    {
        return response.Length <= 2 ? [] : response[..^2];
    }
}
