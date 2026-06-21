namespace Vms.LocalBridge.Models;

public sealed record BadgePrintJob(
    string LogId,
    string VisitorName,
    string CompanyName,
    string HostName,
    string RoomName,
    string PrinterId);
