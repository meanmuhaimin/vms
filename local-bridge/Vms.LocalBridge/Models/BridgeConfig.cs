using System.Text.Json;

namespace Vms.LocalBridge.Models;

public sealed record BridgeConfig(
    CoreApiConfig CoreApi,
    SmartCardConfig SmartCard,
    PrinterConfig Printer,
    OfflineQueueConfig OfflineQueue)
{
    public static BridgeConfig Load(string path)
    {
        if (!File.Exists(path))
        {
            throw new FileNotFoundException($"Bridge configuration file not found: {path}");
        }

        var json = File.ReadAllText(path);
        return JsonSerializer.Deserialize<BridgeConfig>(json, BridgeJson.Options)
            ?? throw new InvalidOperationException("Bridge configuration is empty or invalid.");
    }
}

public sealed record CoreApiConfig(string BaseUrl, string LaneId, string BridgeToken);

public sealed record SmartCardConfig(int PollIntervalMilliseconds, string? PreferredReaderName);

public sealed record PrinterConfig(string PrinterId, string Host, int Port, int ConnectTimeoutMilliseconds);

public sealed record OfflineQueueConfig(string DatabasePath, int MaxSyncBatchSize);
