<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => '予約確認: :service',
            'title' => '予約が確認されました',
            'intro' => 'こんにちは :name、予約が確認されました。',
            'service' => 'サービス',
            'datetime' => '日時',
            'status' => 'ステータス',
            'manage' => '予約を管理',
            'signoff' => 'ありがとうございます、:app',
        ],
        'cancelled' => [
            'subject' => '予約キャンセル: :service',
            'title' => '予約がキャンセルされました',
            'intro' => 'こんにちは :name、予約がキャンセルされました。',
            'service' => 'サービス',
            'datetime' => '日時',
            'status' => 'ステータス',
            'signoff' => 'ありがとうございます、:app',
        ],
        'rescheduled' => [
            'subject' => '予約変更: :service',
            'title' => '予約が変更されました',
            'intro' => 'こんにちは :name、予約が変更されました。',
            'service' => 'サービス',
            'datetime' => '日時',
            'status' => 'ステータス',
            'signoff' => 'ありがとうございます、:app',
        ],
    ],
];
