using System.Text;
using Vms.LocalBridge.Models;

namespace Vms.LocalBridge.Hardware;

public sealed class MyKadSmartCardReader
{
    private readonly SmartCardConfig _config;

    public MyKadSmartCardReader(SmartCardConfig config)
    {
        _config = config;
    }

    public MyKadIdentity? TryReadMyKad()
    {
        using var session = SmartCardSession.TryOpen(_config.PreferredReaderName);
        if (session is null)
        {
            return null;
        }

        var selectResponse = session.Transmit(MyKadApdu.SelectMyKadApplication());
        if (!MyKadApdu.IsSuccess(selectResponse))
        {
            return null;
        }

        var nameBlock = MyKadApdu.Payload(session.Transmit(MyKadApdu.ReadBinary(0x0110, 80)));
        var idBlock = MyKadApdu.Payload(session.Transmit(MyKadApdu.ReadBinary(0x0160, 16)));
        var genderBlock = MyKadApdu.Payload(session.Transmit(MyKadApdu.ReadBinary(0x0170, 4)));

        var fullName = DecodeText(nameBlock);
        var idNumber = DecodeText(idBlock);
        var gender = DecodeText(genderBlock);

        if (string.IsNullOrWhiteSpace(fullName) && string.IsNullOrWhiteSpace(idNumber))
        {
            return null;
        }

        return new MyKadIdentity(
            DocumentType: "MYKAD",
            FullName: fullName,
            IdNumber: idNumber,
            Gender: string.IsNullOrWhiteSpace(gender) ? null : gender,
            ReaderName: session.ReaderName,
            ExtractedAt: DateTimeOffset.UtcNow);
    }

    private static string DecodeText(byte[] payload)
    {
        var text = Encoding.ASCII.GetString(payload);
        return text.Replace("\0", string.Empty).Trim();
    }
}
