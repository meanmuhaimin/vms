using System.Text.Json;
using Vms.LocalBridge.Hardware;
using Vms.LocalBridge.Models;
using Vms.LocalBridge.Offline;
using Vms.LocalBridge.Printing;

var config = BridgeConfig.Load(args.Length > 0 ? args[0] : "appsettings.json");
var cardReader = new MyKadSmartCardReader(config.SmartCard);
var printer = new RawSocketBadgePrinter(config.Printer);
var offlineQueue = new SqliteOfflineQueue(config.OfflineQueue);
await offlineQueue.InitializeAsync(CancellationToken.None);

Console.WriteLine($"VMS Local Bridge started for lane {config.CoreApi.LaneId}.");
Console.WriteLine("Press Ctrl+C to stop.");

using var cancellation = new CancellationTokenSource();
Console.CancelKeyPress += (_, eventArgs) =>
{
    eventArgs.Cancel = true;
    cancellation.Cancel();
};

while (!cancellation.IsCancellationRequested)
{
    try
    {
        var identity = cardReader.TryReadMyKad();
        if (identity is not null)
        {
            var payload = JsonSerializer.Serialize(identity, BridgeJson.Options);
            await offlineQueue.EnqueueAsync("mykad.identity.read", payload, cancellation.Token);
            Console.WriteLine(payload);
        }

        if (File.Exists("print-job.json"))
        {
            var jobJson = await File.ReadAllTextAsync("print-job.json", cancellation.Token);
            var job = JsonSerializer.Deserialize<BadgePrintJob>(jobJson, BridgeJson.Options);
            if (job is not null)
            {
                await printer.PrintAsync(job, cancellation.Token);
                await offlineQueue.EnqueueAsync("badge.printed", JsonSerializer.Serialize(job, BridgeJson.Options), cancellation.Token);
                File.Move("print-job.json", $"print-job.{DateTimeOffset.UtcNow:yyyyMMddHHmmss}.sent.json");
            }
        }
    }
    catch (OperationCanceledException)
    {
        break;
    }
    catch (Exception exception)
    {
        Console.Error.WriteLine($"Bridge loop error: {exception.Message}");
    }

    if (!cancellation.IsCancellationRequested)
    {
        try
        {
            await Task.Delay(config.SmartCard.PollIntervalMilliseconds, cancellation.Token);
        }
        catch (OperationCanceledException)
        {
            break;
        }
    }
}

Console.WriteLine("VMS Local Bridge stopped.");
