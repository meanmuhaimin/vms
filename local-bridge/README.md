# VMS Local Bridge

Phase 2 local reception bridge for hardware integrations.

Responsibilities:

- Poll PC/SC smart card readers through `WinSCard.dll`.
- Select the MyKad application profile using `A0 00 00 00 74 4A 4D 41 53 20 49 44`.
- Read and parse identity blocks into a local `MyKadIdentity` payload.
- Send thermal badge commands to network printers over raw TCP port `9100`.
- Persist identity-read and print-complete events into local SQLite when upstream connectivity is unavailable.

Runtime target: Windows with .NET 8 SDK/runtime and PC/SC-compatible smart card reader drivers.

Setup:

```powershell
copy Vms.LocalBridge\appsettings.example.json Vms.LocalBridge\appsettings.json
dotnet run --project Vms.LocalBridge\Vms.LocalBridge.csproj
```

The current bridge prints a local `print-job.json` file if present. API sync can be wired after the backend exposes reception bridge endpoints.
