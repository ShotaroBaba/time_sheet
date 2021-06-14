# Time Sheet
Docker、MySQL並びにPHPを基にしたの勤怠表を記録するウェブサイトです。ユーザはphpMyAdminを用いて勤怠表のデータを収めているMySQLサーバを管理できます


# インストール前に必要なもの
あなたのオペレーティングシステムはUbuntu (>=18.04 LTS)（こちらのOSでの動作はテスト済み）、CentOS (>= 7.4)、またはUnix系のOSである必要があります。現在CentOSやその他のUnix系のOSでのサーバーの動作はテストしておりません。

インストールに進む前に、下記のパッケージがインストールされている必要があります：

```
Docker (>=19.03.7)
Docker-compose (>=1.24.1)
```
# インストール
以下のコマンドを実行してレポジトリをダウンロードして下さい。

```bash
git clone https://github.com/ShotaroBaba/time_sheet.git
```

インストールするためには下記のコマンドをプロジェクトディレクトリのトップで実行して下さい：
```bash
cd time_sheet
./create_server.sh
```

# Root、その他バスワードへのアクセス

インストールの間、MySQLのroot、admin、time_sheet_adminのアカウントのパスワードは自動的に生成されます。これらパスワードの一つを取り出すためには、プロジェクトディレクトリのトップで下記のコマンドを実行して下さい。

To retrive MySQL 'root' password:
```bash
decrypt_script/root_decrypt.sh
```
別の方法として,クリップボードにパスワードをコピーすることも可能です:
```bash
decrypt_script/root_decrypt.sh | xclip -sel clip
```
#
MySQLの'admin'パスワードを取り出す:
```bash
decrypt_script/root_decrypt.sh
```
別のやり方
)
```bash
decrypt_script/root_decrypt.sh | xclip -sel clip
```
#
MySQLの'timesheet_admin'パスワードを取り出す:
```bash
decrypt_script/timesheet_admin_decrypt.sh
```
別のやり方
```bash
decrypt_script/timesheet_admin_decrypt.sh | xclip -sel cli
```
# サーバの管理

サーバのシャットダウン
```bash
./shutdown_server.sh
```

サーバの起動
```bash
./run_server.sh
```

サーバの停止
```bash
docker-compose down
```

サーバのアンインストール
```bash
./uninstall_server.sh
```
サーバのアンインストール後はデータのバックアップ等はできないことを留意ください。

# サーバへのアクセス
ウェブサイトへは以下のアドレスをブラウザに入力することによりアクセスできます。
```
localhost:59111
```
#
phpMyAdminへアクセスする場合は以下のアドレスを入力下さい。
```bash
localhost:55553
```

デフォルトのポート番号を変えたい場合はenv_defaulファイル内にあるphpMyAdminサーバのPHPMYADMIN_PORT (デフォルト: 5 55553)のポートを、勤怠表のウェブサーバのポートを変えたい場合はHTTP_PORT (デフォルト: 59111)を変えて下さい。

# To Doリスト
- バックアップスクリプトの作成
- すべてのバクのチェックと除去
- HTTPSアクセスを可能とするスクリプトの作成
- 使いやすさのためのウェブサイトの体裁の調整
- README.md　システム、サーバ、README.mdファイルを詳細にチェック
- 日本語バージョンのREADME.mdとサーバの作成


# Source
  Timesheet icon [image](https://publicdomainvectors.org/en/free-clipart/Paper-sheet/59351.html) (https://publicdomainvectors.org/en/free-clipart/Paper-sheet/59351.html)