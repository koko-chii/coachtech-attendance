# coachtech-attendance

このリポジトリは、laravelを使用した 実践学習ターム模擬案件中級＿勤怠管理アプリです。

## 作成者

松井　由美子

## 使用技術

- フレームワーク：Laravel 12.0x

- 言語：PHP 8.2

- Webサーバー：Nginx

- データベース：MySQL 8.0

- コンテナ管理 :Docker Compose

- DB管理ツール:phpMyAdmin

- メールテスト:MailHog

## ER図

```mermaid
erDiagram
    users ||--o{ passkeys : ""
    users ||--o{ attendance_records : ""
    users ||--o{ stamp_correction_requests : ""
    attendance_records ||--o{ breaks : ""
    attendance_records ||--o{ stamp_correction_requests : ""

    users {
        bigint id
        string name
        string email
        timestamp email_verified_at
        string password
        boolean admin_status
        timestamp created_at
        timestamp updated_at
    }

    passkeys {
        unsigned_bigint id
        unsigned_bigint user_id
        string name
        string credential_id
        json credential
        timestamp last_used_at
        timestamp created_at
        timestamp updated_at
    }

    admins {
        unsigned_bigint id
        string name
        string email
        string password
        timestamp created_at
        timestamp updated_at
    }

    attendance_records {
        unsigned_bigint id
        unsigned_bigint user_id
        date date
        time clock_in
        time clock_out
        text comment
        timestamp created_at
        timestamp updated_at
    }

    breaks {
        unsigned_bigint id
        unsigned_bigint foreignId
        time break_in
        time break_out
        timestamp created_at
        timestamp updated_at
    }

    stamp_correction_requests {
        unsigned_bigint id
        unsigned_bigint user_id
        unsigned_bigint attendance_record_id
        time requested_clock_in
        time requested_clock_out
        json requested_breaks
        text comment
        string status
        timestamp created_at
        timestamp updated_at
    }

```

## 開発環境URL

http://○○○○○

## 動作環境

○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

## 環境構築手順

1. **リポジトリをクローン**

    ```bash
    git clone https://○○○○○○
    ```

2. **.envファイルの準備**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

3. **Composer依存パッケージのインストール**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

4. **Laravel Sailの起動**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

5. **アプリケーションキーの生成**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

6. **データベースのマイグレーションと初期データ投入**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

7. **フロントエンドのビルド**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

8. **アプリケーションへのアクセス**

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

## テスト実行

    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○
    ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

## 機能一覧

- ○○○○○○ ○○○○○○
- ○○○○○○ ○○○○○○
- ○○○○○○ ○○○○○○
- ○○○○○○ ○○○○○○
- ○○○○○○ ○○○○○○
- ○○○○○○ ○○○○○○

## APIエンドポイント一覧

○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○ ○○○○○○

| HTTPメソッド | URI | 概要 |
|---|---|---|
| GET | /○○○○○○/○○○○○○/○○○○○○ | ○○○○○○ |
| GET | /○○○○○○/○○○○○○/○○○○○○ | ○○○○○○ |
| GET | /○○○○○○/○○○○○○/○○○○○○ | ○○○○○○ |
| GET | /○○○○○○/○○○○○○/○○○○○○ | ○○○○○○ |
| GET | /○○○○○○/○○○○○○/○○○○○○ | ○○○○○○ |

