using System.Text.Json;

namespace Vms.LocalBridge.Models;

public static class BridgeJson
{
    public static readonly JsonSerializerOptions Options = new(JsonSerializerDefaults.Web)
    {
        WriteIndented = true,
    };
}
