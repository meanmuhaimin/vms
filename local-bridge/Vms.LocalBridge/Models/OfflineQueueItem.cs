namespace Vms.LocalBridge.Models;

public sealed record OfflineQueueItem(
    long Id,
    string QueueType,
    string PayloadJson,
    DateTimeOffset CreatedAt,
    DateTimeOffset? SyncedAt);
