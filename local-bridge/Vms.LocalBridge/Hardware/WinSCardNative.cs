using System.Runtime.InteropServices;

namespace Vms.LocalBridge.Hardware;

internal static partial class WinSCardNative
{
    public const uint ScopeUser = 0x0000;
    public const uint ShareShared = 0x0002;
    public const uint ProtocolT0 = 0x0001;
    public const uint ProtocolT1 = 0x0002;
    public const uint LeaveCard = 0x0000;
    public const int Success = 0;

    [StructLayout(LayoutKind.Sequential)]
    public struct IoRequest
    {
        public uint Protocol;
        public uint PciLength;
    }

    [LibraryImport("winscard.dll")]
    public static partial int SCardEstablishContext(
        uint dwScope,
        nint pvReserved1,
        nint pvReserved2,
        out nint phContext);

    [LibraryImport("winscard.dll")]
    public static partial int SCardReleaseContext(nint hContext);

    [LibraryImport("winscard.dll", StringMarshalling = StringMarshalling.Utf16)]
    public static partial int SCardListReaders(
        nint hContext,
        string? mszGroups,
        char[]? mszReaders,
        ref int pcchReaders);

    [LibraryImport("winscard.dll", StringMarshalling = StringMarshalling.Utf16)]
    public static partial int SCardConnect(
        nint hContext,
        string szReader,
        uint dwShareMode,
        uint dwPreferredProtocols,
        out nint phCard,
        out uint pdwActiveProtocol);

    [LibraryImport("winscard.dll")]
    public static partial int SCardDisconnect(nint hCard, uint dwDisposition);

    [DllImport("winscard.dll")]
    public static extern int SCardTransmit(
        nint hCard,
        ref IoRequest pioSendPci,
        byte[] pbSendBuffer,
        int cbSendLength,
        nint pioRecvPci,
        byte[] pbRecvBuffer,
        ref int pcbRecvLength);
}
