logged_ips = set()

def request(flow):
    client_ip = flow.client_conn.address[0]
    if client_ip not in logged_ips:
        logged_ips.add(client_ip)
        with open("unique_client_ips.log", "a") as log_file:
            log_file.write(f"{client_ip}\n")
