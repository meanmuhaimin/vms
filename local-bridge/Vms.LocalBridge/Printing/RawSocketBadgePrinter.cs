using System.Net.Sockets;
using System.Text;
using Vms.LocalBridge.Models;

namespace Vms.LocalBridge.Printing;

public sealed class RawSocketBadgePrinter
{
    private readonly PrinterConfig _config;

    public RawSocketBadgePrinter(PrinterConfig config)
    {
        _config = config;
    }

    public async Task PrintAsync(BadgePrintJob job, CancellationToken cancellationToken)
    {
        if (!string.Equals(job.PrinterId, _config.PrinterId, StringComparison.OrdinalIgnoreCase))
        {
            throw new InvalidOperationException($"Print job targets {job.PrinterId}, but this bridge is configured for {_config.PrinterId}.");
        }

        var zpl = ZplBadgeTemplate.Render(job);
        var payload = Encoding.ASCII.GetBytes(zpl);

        using var client = new TcpClient();
        var connectTask = client.ConnectAsync(_config.Host, _config.Port, cancellationToken).AsTask();
        var timeoutTask = Task.Delay(_config.ConnectTimeoutMilliseconds, cancellationToken);

        if (await Task.WhenAny(connectTask, timeoutTask) != connectTask)
        {
            throw new TimeoutException($"Timed out connecting to printer {_config.Host}:{_config.Port}.");
        }

        await using var stream = client.GetStream();
        await stream.WriteAsync(payload, cancellationToken);
        await stream.FlushAsync(cancellationToken);
    }
}
