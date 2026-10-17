# GymFit Management

Ứng dụng quản lý hội viên phòng gym dùng PHP, MySQL và Docker Compose; Nginx làm reverse proxy, Prometheus/Grafana giám sát và Loki/Promtail tập trung log.

## Yêu cầu

- Docker Desktop với Docker Compose v2
- Các cổng localhost `8080`, `8081`, `3000` và `9090` chưa được ứng dụng khác sử dụng

## Chạy lần đầu

1. Sao chép `.env.example` thành `.env`.
2. Thay tất cả giá trị mẫu bằng mật khẩu riêng, dài và khác nhau. Không commit hoặc gửi file `.env`.
3. Chạy `docker compose up -d --build`.
4. Kiểm tra trạng thái bằng `docker compose ps`.

| Thành phần | Địa chỉ |
| --- | --- |
| Ứng dụng GymFit | <http://localhost:8080> |
| phpMyAdmin | <http://localhost:8081> |
| Grafana | <http://localhost:3000> |
| Prometheus | <http://localhost:9090> |

Đăng nhập phpMyAdmin bằng `gym_user` và `DB_PASSWORD` trong `.env`. Grafana dùng tên đăng nhập `admin` và mật khẩu `GRAFANA_PASSWORD`. MySQL không publish cổng ra máy chủ; chỉ các ứng dụng trong mạng Docker mới kết nối được.

## Dashboard và log

Dashboard **GymFit - Overview** được provision tự động trong thư mục GymFit của Grafana. Nó hiển thị số request Nginx, bộ nhớ container, kết nối MySQL và log tập trung.

Trong Grafana Explore, chọn datasource Loki và chạy:

```logql
{job="docker"}
```

Các log Docker dùng `json-file` với giới hạn 10 MB mỗi file và tối đa 3 file/container. Loki giữ log trong 7 ngày. Promtail đọc log từ Docker Engine; trên Docker Desktop, dữ liệu metrics/log container có thể khác Linux host tùy môi trường Docker.

## Bảo mật và mạng

- Nginx unprivileged là điểm truy cập web duy nhất; ứng dụng PHP chạy bằng `www-data` và lắng nghe nội bộ trên cổng 8080.
- Các cổng được bind vào `127.0.0.1`; MySQL không có port mapping ra host.
- Database và monitoring dùng các mạng Docker nội bộ riêng; database không nối trực tiếp với mạng web.
- MySQL exporter dùng tài khoản riêng chỉ có `PROCESS`, `REPLICATION CLIENT` và `SELECT`.
- Nginx đặt security headers; reverse proxy hiện dùng HTTP localhost, không cấu hình HTTPS.
- Không bật `privileged` cho cAdvisor; một số metrics hệ thống có thể bị giới hạn tùy host/container runtime.
- Thay mật khẩu ngay nếu `.env` bị chia sẻ; các mật khẩu trong `.env` không tự đổi theo mẫu `.env.example`.

## Kiểm tra nhanh

```powershell
docker compose config -q
docker compose ps
docker compose logs --tail 100 app nginx db prometheus grafana loki promtail mysqld-exporter
```

Prometheus có các target cho Prometheus, cAdvisor, Nginx exporter, MySQL exporter và Loki. Mục **Status → Targets** cần có trạng thái `UP`.

## Dừng dịch vụ

```powershell
docker compose down
```

Lệnh trên giữ dữ liệu MySQL, Prometheus, Grafana và Loki trong named volumes. Không dùng `docker compose down -v` nếu cần giữ dữ liệu.