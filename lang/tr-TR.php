<?php
/*
 * Bu çeviri dosyaları, UnrealIRCd Web Panel arayüzü için özel olarak Türkçeye çevrilmiştir.
 * Amaç, kullanıcıların yönetim panelini Türkçe dilinde rahatça kullanabilmesini sağlamaktır.
 * Tüm metinler, UnrealIRCd Web Panel’in orijinal İngilizce sürümünden çevrilmiştir.
 * Geliştirici/çevirmen tarafından herhangi bir resmi UnrealIRCd desteği sağlanmamaktadır.
 *
 * Daha fazla bilgi için UnrealIRCd Web Panel belgelerine başvurabilirsiniz:
 * https://www.unrealircd.org/docs/
 *
 * Çeviri: [Ömer ATABER - OmerAti - omerati6363@gmail.com - info@jrodix.com]
 * Tarih:  [08.06.2025]
 */

return [
    'language_name' => 'Türkçe',
	
    // Login Page
    'login_title' => 'Admin Paneli kullanmak için giriş yapın',
    'username_empty' => 'Kullanıcı adı boş bırakılamaz',
    'password_empty' => 'Parola boş bırakılamaz',
    'user_login_fail' => 'Giriş bilgileri yanlış',
    'user_login_timeout' => 'Oturumunuz zaman aşımına uğradı. Devam etmek için tekrar giriş yapınız',
    'user_login_no_id' => 'Çıkış yapılacak oturum bulunamadı',
    'user_login_missing' => 'Giriş yapılamadı: Eksik bilgiler',
    'user_login_logged' => 'Oturumunuz kapatıldı',
    'login_button' => 'Giriş Yap',
	
	// Requirements	
	'requirements_php_version' => 'Bu web sunucusu şu PHP sürümünü kullanıyor: %s, ancak en az PHP 8.0.0 gerekmektedir.<br>'.
    'Eğer PHP 8\'i zaten kurduysanız ve hala bu hatayı alıyorsanız, bu apache/nginx/... eski bir PHP sürümünü yüklüyor demektir. '.
    'Örneğin Debian/Ubuntu üzerinde <code>apt-get install libapache2-mod-php8.2</code> (veya benzer bir sürüm) ve '.
    '<code>apt-get remove libapache2-mod-php7.4</code> (veya benzer bir sürüm) komutlarını çalıştırmalısınız. '.
    'Ayrıca apache için yüklenecek PHP modülünü <code>a2enmod php8.2</code> komutuyla tekrar seçmeniz gerekebilir.',

	'requirements_extensions_missing_title' => 'Aşağıdaki PHP modüllerinin yüklü olması gerekiyor:',
	'requirements_extensions_missing_cmd' => 'Bu PHP paketlerini kurmanız/etkinleştirmeniz ve web sunucusunu yeniden başlatmanız gerekiyor.<br>'.
		'Debian/Ubuntu kullanıyorsanız <code>%s</code> komutunu çalıştırın ve web sunucunuzu yeniden başlatın (ör: apache için <code>systemctl restart apache2</code>).',
	'requirements_config_file_notice_1' => 'Bu yapılandırma dosyası UnrealIRCd web panel tarafından otomatik olarak yazılmıştır.',
	'requirements_config_file_notice_2' => 'Bu dosyayı manuel olarak düzenlemeniz önerilmez.',
	'requirements_write_config_rename_error' => 'Dosyaya yazılamadı (yeniden adlandırma hatası): %s.<br>',
	'requirements_config_write_error' => 'Geçici yapılandırma dosyasına yazılamadı: %s.<br>config/ dizini üzerinde yazma izni gereklidir!<br>',
	'requirements_write_config_weird' => 'write_config_file() çalıştırılırken hata oluştu -- garip!',
	'requirements_write_config_fwrite' => 'Yapılandırma dosyasına yazılırken hata oluştu %s (fwrite sırasında).<br>',
	'requirements_write_config_fclose' => 'Yapılandırma dosyasına yazılırken hata oluştu %s (kapatılırken).<br>',
	
	// Menu
	'menu_overview' => 'Genel Bakış',
	'menu_users' => 'Kullanıcılar',
	'menu_channels' => 'Kanallar',
	'menu_servers' => 'Sunucular',
	'a_menu_servers_bans' => 'Sunucu Yasakları',
	'menu_server_ban' => 'Sunucu Yasakları',
	'menu_name_bans' => 'İsim Yasakları',
	'menu_ban_exceptions' => 'Yasak İstisnaları',
	'menu_spamfilter' => 'Spam Filtresi',
	'menu_logs' => 'Kayıtlar',
	'a_menu_tools' => 'Araçlar',
	'menu_ip_whois' => 'IP Whois',
	'a_menu_settings' => 'Ayarlar',
	'menu_general_settings' => 'Genel Ayarlar',
	'menu_rpc_servers' => 'RPC Sunucuları',

	// RPC Sunucuları
	'rpc_servers_title' => 'RPC Sunucuları',
	'rpc_servers_description_1' => 'Panelin bağlanabileceği JSON-RPC sunucularını yapılandırabilirsiniz.',
	'rpc_servers_description_2' => 'Genellikle bir sunucu yeterlidir, ancak birincil sunucu devre dışı kalırsa yedek sunucuya geçmek için birden fazla sunucu faydalı olabilir.',
	'rpc_servers_link_panel_info' => 'Panelinizi UnrealIRCd ile bağlayalım. <u><a href="https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Configuring_UnrealIRCd" target="_blank">UnrealIRCd talimatlarını</a></u> okuyun ve ardından aşağıdan <i>Sunucu Ekle</i> ye tıklayın.',
	'rpc_servers_delete_failed' => 'Silme başarısız: sunucu bulunamadı',
	'rc_servers_already_exists' => 'Bu isimde bir sunucu zaten mevcut',
	'rpc_servers_not_exist' => 'Var olmayan bir sunucu düzenleniyor!?',
	'rpc_servers_added' => 'RPC Sunucusu başarıyla eklendi.',
	'rpc_servers_modified' => 'RPC Sunucusu başarıyla değiştirildi.',
	'rpc_add_server_title' => 'RPC Sunucusu Ekle',
	'rpc_add_display_name' => 'Görünen isim',
	'rpc_add_display_name_help' => 'RPC sunucu listesinde gösterilecek kısa isim.',
	'rpc_add_default_server' => 'Varsayılan sunucu',
	'rpc_add_default_server_help' => 'Bağlantılar için kullanılacak varsayılan (birincil) sunucu yap.',
	'rpc_add_default_hostname' => 'Hostname veya IP',
	'rpc_add_default_hostname_help' => 'UnrealIRCd sunucunuzun hostname veya IP adresi. Aynı makine için <code>127.0.0.1</code> kullanmalısınız.',
	'rpc_add_server_port' => 'Sunucu Portu',
	'rpc_add_server_port_help' => '<code>unrealircd.conf</code> dosyanızda RPC bağlantıları için belirlediğiniz port.',
	'rpc_add_certificate' => 'SSL/TLS sertifikasını doğrula',
	'rpc_tls_verify_cert_help' => 'Sadece hostname ile kullanılabilir, 127.0.0.1 için etkinleştirmeyin.',
	'rpc_add_username' => 'Kullanıcı adı',
	'rpc_add_username_help' => '<code>unrealircd.conf</code> dosyanızda tanımlı <code>rpc-user</code> bloğunuzun adı',
	'rpc_add_password' => 'Parola',
	'rpc_add_cancel' => 'İptal',
	'rpc_add_servers' => 'Sunucu Ekle',
	'rpc_add_server_add' => 'Sunucu Ekle',
	'rpc_add_server_edit' => 'Düzenle',
	'rpc_add_error_notice_1' => 'RPC Sunucu hatası',
	'rpc_add_error_notice_2' => 'RPC Sunucusuna bağlanılamadı. Ayarları kontrol edin ve tekrar deneyin.',
	'rpc_add_error_no' => 'Hayır',
	'rpc_confirm_deletion' => 'Silme işlemini onayla',
	'rpc_confirm_deletion_notice' => 'Bu sunucuyu silmek istediğinize emin misiniz?',
	'rpc_confirm_deletion_cancel' => 'İptal',
	'rpc_confirm_delete_server' => 'Sunucuyu Sil',
	'rpc_display_name' => 'Görünen isim',
	'rpc_display_hostname' => 'Hostname',
	'rpc_display_port' => 'Port',
	'rpc_display_rpcuser' => 'RPC Kullanıcısı',
	
	// Other
	'other_base_url' => 'config dosyanızda base_url bulunamadı. Kurulumda bir hata mı oldu?',
	'other_auth_provided' => 'Herhangi bir kimlik doğrulama eklentisi yüklenmedi. sql_db, file_db veya benzeri bir kimlik doğrulama eklentisi yüklemelisiniz!',

];
