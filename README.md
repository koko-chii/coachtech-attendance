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

-メールテスト:MailHog

## ER図

```mermaid
erDiagram




    users {
        id
        name
        email
        email_verified_at
        password
        two_factor_secret
        two_factor_recovery
        two_factor_confirmed
        admin_status
        rememberToken
        created_at
        updated_at
    }

    passkeys {
        id
        user_id
        neme
        credential_id
        credential
        last_used_at
        created_at
        updated_at
        neme
    }

    admin {
        id
        name
        email
        password
        created_at
        updated_at
    }

    attendance_recoeds {
        id
        user_id
        date
        clodk_in
        clodk_out
        comment
        created_at
        updated_at  
    }

    breaks {
        id
        foreignId
        break_in
        break_out
        created_at
        uupdated_at
    }

    stamp_correction_requests {
        id
        user_id
        attendance_record_id
        requested_clock_in
        requested_clock_out
        requested_breaks
        comment
        status
        created_at
        updated_at
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

