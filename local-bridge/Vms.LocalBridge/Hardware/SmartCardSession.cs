using System.Runtime.InteropServices;

namespace Vms.LocalBridge.Hardware;

internal sealed class SmartCardSession : IDisposable
{
    private readonly nint _context;
    private readonly nint _card;
    private readonly uint _protocol;
    private bool _disposed;

    private SmartCardSession(nint context, nint card, uint protocol, string readerName)
    {
        _context = context;
        _card = card;
        _protocol = protocol;
        ReaderName = readerName;
    }

    public string ReaderName { get; }

    public static SmartCardSession? TryOpen(string? preferredReaderName)
    {
        var contextResult = WinSCardNative.SCardEstablishContext(
            WinSCardNative.ScopeUser,
            nint.Zero,
            nint.Zero,
            out var context);

        if (contextResult != WinSCardNative.Success)
        {
            return null;
        }

        try
        {
            var readers = ListReaders(context);
            var reader = string.IsNullOrWhiteSpace(preferredReaderName)
                ? readers.FirstOrDefault()
                : readers.FirstOrDefault(item => item.Contains(preferredReaderName, StringComparison.OrdinalIgnoreCase));

            if (reader is null)
            {
                WinSCardNative.SCardReleaseContext(context);
                return null;
            }

            var connectResult = WinSCardNative.SCardConnect(
                context,
                reader,
                WinSCardNative.ShareShared,
                WinSCardNative.ProtocolT0 | WinSCardNative.ProtocolT1,
                out var card,
                out var activeProtocol);

            if (connectResult == WinSCardNative.Success)
            {
                return new SmartCardSession(context, card, activeProtocol, reader);
            }

            WinSCardNative.SCardReleaseContext(context);
            return null;
        }
        catch
        {
            WinSCardNative.SCardReleaseContext(context);
            throw;
        }
    }

    public byte[] Transmit(byte[] command)
    {
        var sendPci = new WinSCardNative.IoRequest
        {
            Protocol = _protocol,
            PciLength = (uint)Marshal.SizeOf<WinSCardNative.IoRequest>(),
        };

        var response = new byte[258];
        var responseLength = response.Length;
        var result = WinSCardNative.SCardTransmit(
            _card,
            ref sendPci,
            command,
            command.Length,
            nint.Zero,
            response,
            ref responseLength);

        if (result != WinSCardNative.Success)
        {
            throw new InvalidOperationException($"SCardTransmit failed with code {result}.");
        }

        return response.Take(responseLength).ToArray();
    }

    public void Dispose()
    {
        if (_disposed)
        {
            return;
        }

        WinSCardNative.SCardDisconnect(_card, WinSCardNative.LeaveCard);
        WinSCardNative.SCardReleaseContext(_context);
        _disposed = true;
    }

    private static IReadOnlyList<string> ListReaders(nint context)
    {
        var readerLength = 0;
        var result = WinSCardNative.SCardListReaders(context, null, null, ref readerLength);
        if (result != WinSCardNative.Success || readerLength <= 0)
        {
            return [];
        }

        var readerBuffer = new char[readerLength];
        result = WinSCardNative.SCardListReaders(context, null, readerBuffer, ref readerLength);
        if (result != WinSCardNative.Success)
        {
            return [];
        }

        return new string(readerBuffer)
            .Split('\0', StringSplitOptions.RemoveEmptyEntries)
            .ToArray();
    }
}
