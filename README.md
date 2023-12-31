# 檔案儲存服務

> [返回根目錄](https://github.com/samuikaze/my-work-2023)

這是檔案儲存服務的專案，使用 Lumen Framework (PHP) 撰寫而成

## 說明

由於後端拆分許多專案，針對檔案儲存需要有一個服務專門處理這類需求，因而有此專案的產生。

## 事前準備

使用本專案前請先安裝以下軟體

- php 8.1 或以上
- composer 2.0 或以上
- MySQL 或 MariaDB
- Nginx 或 Apache

## 線上展示

- [點此檢視 Swagger 展示](https://syskzworks.ddns.net/forwork/services/filestorageservice/api/swagger)

## 本機除錯

可以遵循以下步驟在本機進行除錯或檢視

> ⚠️請注意，`.env` 檔中的相關設定請依據需求作修改

1. `git clone` 將本專案 clone 到本機
2. 打開終端機，切換到本專案資料夾
3. 執行指令 `composer install && composer dump-autoload`
4. 啟動 `nginx` 或 `Apache` 伺服器

  > 也可使用 `php artisan serve` 啟動服務，但此方式在 CORS 預檢請求會得到 404 回應，目前仍未找出問題...

## 參考資料

- [Examples - DarkaOnLine/L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger/wiki/Examples)
- [Missing file browser in "Try it out" for multipart/form file uploads](https://github.com/swagger-api/swagger-ui/issues/3784)
- [securityContext fsGroup has no effect](https://github.com/rancher/local-path-provisioner/issues/41)
- [chown: changing ownership of '/data/db': Operation not permitted](https://stackoverflow.com/a/51203031)
- [How can I find my User ID (UID) from terminal?](https://askubuntu.com/a/468239)
- [Init Containers in Kubernetes](https://www.baeldung.com/linux/kubernetes-init-containers)
