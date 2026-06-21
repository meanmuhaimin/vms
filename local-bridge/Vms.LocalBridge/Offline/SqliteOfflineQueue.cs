using Microsoft.Data.Sqlite;
using Vms.LocalBridge.Models;

namespace Vms.LocalBridge.Offline;

public sealed class SqliteOfflineQueue
{
    private readonly OfflineQueueConfig _config;
    private readonly string _connectionString;

    public SqliteOfflineQueue(OfflineQueueConfig config)
    {
        _config = config;
        _connectionString = new SqliteConnectionStringBuilder { DataSource = config.DatabasePath }.ToString();
    }

    public async Task InitializeAsync(CancellationToken cancellationToken)
    {
        await using var connection = new SqliteConnection(_connectionString);
        await connection.OpenAsync(cancellationToken);

        await using var command = connection.CreateCommand();
        command.CommandText = """
            CREATE TABLE IF NOT EXISTS offline_queue (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                queue_type TEXT NOT NULL,
                payload_json TEXT NOT NULL,
                created_at TEXT NOT NULL,
                synced_at TEXT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_offline_queue_unsynced
                ON offline_queue (queue_type, synced_at, created_at);
            """;

        await command.ExecuteNonQueryAsync(cancellationToken);
    }

    public async Task EnqueueAsync(string queueType, string payloadJson, CancellationToken cancellationToken)
    {
        await using var connection = new SqliteConnection(_connectionString);
        await connection.OpenAsync(cancellationToken);

        await using var command = connection.CreateCommand();
        command.CommandText = """
            INSERT INTO offline_queue (queue_type, payload_json, created_at)
            VALUES ($queue_type, $payload_json, $created_at)
            """;
        command.Parameters.AddWithValue("$queue_type", queueType);
        command.Parameters.AddWithValue("$payload_json", payloadJson);
        command.Parameters.AddWithValue("$created_at", DateTimeOffset.UtcNow.ToString("O"));

        await command.ExecuteNonQueryAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<OfflineQueueItem>> GetPendingAsync(CancellationToken cancellationToken)
    {
        var items = new List<OfflineQueueItem>();

        await using var connection = new SqliteConnection(_connectionString);
        await connection.OpenAsync(cancellationToken);

        await using var command = connection.CreateCommand();
        command.CommandText = """
            SELECT id, queue_type, payload_json, created_at, synced_at
            FROM offline_queue
            WHERE synced_at IS NULL
            ORDER BY created_at ASC
            LIMIT $limit
            """;
        command.Parameters.AddWithValue("$limit", _config.MaxSyncBatchSize);

        await using var reader = await command.ExecuteReaderAsync(cancellationToken);
        while (await reader.ReadAsync(cancellationToken))
        {
            items.Add(new OfflineQueueItem(
                reader.GetInt64(0),
                reader.GetString(1),
                reader.GetString(2),
                DateTimeOffset.Parse(reader.GetString(3)),
                reader.IsDBNull(4) ? null : DateTimeOffset.Parse(reader.GetString(4))));
        }

        return items;
    }

    public async Task MarkSyncedAsync(long id, CancellationToken cancellationToken)
    {
        await using var connection = new SqliteConnection(_connectionString);
        await connection.OpenAsync(cancellationToken);

        await using var command = connection.CreateCommand();
        command.CommandText = "UPDATE offline_queue SET synced_at = $synced_at WHERE id = $id";
        command.Parameters.AddWithValue("$synced_at", DateTimeOffset.UtcNow.ToString("O"));
        command.Parameters.AddWithValue("$id", id);

        await command.ExecuteNonQueryAsync(cancellationToken);
    }
}
