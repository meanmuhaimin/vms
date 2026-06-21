using System.Text;
using Vms.LocalBridge.Models;

namespace Vms.LocalBridge.Printing;

public static class ZplBadgeTemplate
{
    public static string Render(BadgePrintJob job)
    {
        var visitorName = Escape(job.VisitorName);
        var companyName = Escape(job.CompanyName);
        var hostName = Escape(job.HostName);
        var roomName = Escape(job.RoomName);
        var logId = Escape(job.LogId);

        var zpl = new StringBuilder();
        zpl.AppendLine("^XA");
        zpl.AppendLine("^FO50,50^A0N,40,40^FD VISITOR DIGITAL PASS^FS");
        zpl.AppendLine($"^FO50,110^A0N,28,28^FD NAME: {visitorName}^FS");
        zpl.AppendLine($"^FO50,150^A0N,28,28^FD COMP: {companyName}^FS");
        zpl.AppendLine($"^FO50,190^A0N,28,28^FD HOST: {hostName}^FS");
        zpl.AppendLine($"^FO50,230^A0N,28,28^FD ROOM: {roomName}^FS");
        zpl.AppendLine($"^FO450,70^BQN,2,6^FDQA,{logId}^FS");
        zpl.AppendLine("^XZ");
        return zpl.ToString();
    }

    private static string Escape(string value)
    {
        return value.Replace("^", string.Empty).Replace("~", string.Empty).Trim();
    }
}
