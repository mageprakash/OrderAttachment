# Order Attachments

Magento Order Attachments module adds ability to attach files to order. It also
provides ability to include attachment links to email template. All attached
files are protected from public access and accessible via private generated
links only.

See more info at our [docs](http://docs.splabs.com/m2/extensions/order-attachments/)

#### Installation

```bash
cd <magento_root>
composer config repositories.sp composer https://sp.github.io/packages/
composer require sp/orderattachment:dev-master --prefer-source
bin/magento module:enable\
    Sp_Core\
    Sp_Checkout\
    Sp_Orderattachment
bin/magento setup:upgrade
```
