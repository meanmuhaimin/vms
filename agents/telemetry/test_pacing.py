from pacing import calculate_dispatch


def test_warmup_day_one_caps_at_fifty_messages():
    command = calculate_dispatch({
        "dispatch_id": "dispatch-1",
        "queue_depth": 80,
        "requested_batch_size": 80,
        "sent_today": 0,
        "account_age_days": 1,
    })

    assert command["allowed_count"] == 50
    assert command["fallback_to_sms"] is True
    assert 2000 <= command["delay_ms"] <= 5000


def test_standard_mode_allows_requested_batch():
    command = calculate_dispatch({
        "dispatch_id": "dispatch-2",
        "queue_depth": 20,
        "requested_batch_size": 10,
        "sent_today": 500,
        "account_age_days": 15,
    })

    assert command["allowed_count"] == 10
    assert command["fallback_to_sms"] is False
    assert 2000 <= command["delay_ms"] <= 5000
