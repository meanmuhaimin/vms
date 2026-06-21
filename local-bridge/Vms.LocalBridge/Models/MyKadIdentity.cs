namespace Vms.LocalBridge.Models;

public sealed record MyKadIdentity(
    string DocumentType,
    string FullName,
    string IdNumber,
    string? Gender,
    string ReaderName,
    DateTimeOffset ExtractedAt);
