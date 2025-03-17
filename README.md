# PrestaShop Garanti BBVA v1.7.x ve v8.x Ödeme Modülü Entegrasyonu

PrestaShop, e-ticaret siteleri için popüler bir açık kaynaklı platformdur. Garanti BBVA ödeme modülü ile müşterilerinize güvenli ödeme seçenekleri sunabilirsiniz. 
Aşağıda, Garanti BBVA modül kurulum sürecini adım adım anlatan bir kılavuz bulunmaktadır.

## EKLENTİ İNDİRME

[Buraya](https://github.com/eticsoft/garantibbva-prestashop-module/releases) tıklayıp açılan sayfada en son sürümü seçin ardından garantibbva.zip adlı dosyayı indirebilirsiniz.

![Prestashop eklenti indirme](https://cdn.paythor.com/3/102/installation/install.png)

## EKLENTİ YÜKLEME

1. Prestashop yönetici panelinize giriş yapın.
2. Sol menüden Modüller > Modül Yöneticisi sekmesine tıklayın.
3. Sayfanın sağ üst köşesinde bulunan Modül Yükle butonuna tıklayın.
4. Açılan pencerede, bilgisayarınıza indirdiğiniz Garanti BBVA Modülü ZIP dosyanızı seçin ve yüklemenin tamamlanmasını bekleyin. 

![Prestashop kurulum adım 1](https://cdn.paythor.com/3/102/installation/1.png)

5. Yükleme tamamlandıktan sonra Yapılandır butonuna tıklayın.

![Prestashop kurulum adım 2](https://cdn.paythor.com/3/102/installation/2.png)


### FTP Üzerinden Modül Yükleme (Alternatif Yöntem)

Eğer yönetici paneli üzerinden yükleme başarısız olursa, modülü manuel olarak yüklemek için aşağıdaki adımları takip edin:

1. FileZilla veya benzeri bir FTP istemcisi kullanarak sunucunuza bağlanın.
2. `modules` dizinine gidin (`/var/www/html/modules/` veya `/public_html/modules/`).
3. ZIP dosyanızı bilgisayarınıza çıkarın.
4. Çıkarılan `garantibbva` klasörünü `modules` dizinine yükleyin.

![FTP kurulum görseli](https://cdn.paythor.com/3/102/installation/ftp.png)


5. Yönetici paneline giriş yaparak **Modüller** > **Modül Yöneticisi** sekmesine gidin.
6. Garanti BBVA modülünü listeden bulun ve Yükle butonuna tıklayın.

## AYARLARIN YAPILANDIRILMASI

1. Yönetici panelinden Modüller > Modül Yöneticisi sekmesine gidin.
2. Garanti BBVA modülünün yanındaki Yapılandır butonuna tıklayın.
3. GATEWAY butonuna tıklayın.
4. Garanti BBVA tarafından iletilen bilgileri girin.
5. Yapılandırmaları girdikten sonra Kaydet butonuna basın.

Test siparişi oluşturarak Garanti BBVA ödeme işleminin sorunsuz çalıştığını doğrulayın.
## TEST AŞAMASI

1. GATEWAY butonuna tıklayın.
2. Test Modu başlığının altında yer alan seçilebilir alanı Test Modu olarak seçin ve Kaydet butonuna tıklayın.
3. Sepetinize bir ürün ekleyin ve ödeme adımında GarantiBBVA ile Öde seçeneğini seçin.
4. Açılan Pop-up ödeme sayfası üzerinde test kart bilgilerini giriş yapın ve ödemeyi tamamlayın.

Bu işlemlerden sonra problem yaşanır ise **SUPPORT** butonuna tıklayarak destek ekibi ile iletişime geçebilirsiniz.
