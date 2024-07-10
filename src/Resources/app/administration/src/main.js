import './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/export-templates';
import './app/component/base/sw-icon/index'

import './module/sw-sales-channel';

import './module/helret-cms/blocks/commerce/helloretail';

if (module.hot) {
    module.hot.accept();
}
