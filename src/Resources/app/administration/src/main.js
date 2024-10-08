import './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/export-templates';
import './app/component/base/sw-icon/index'

import './module/sw-sales-channel';

import './module/helret-cms/blocks/hello-retail/helloretail';
import './module/helret-cms/blocks/hello-retail/hello-retail-recommendations';
import './module/helret-cms/elements/hello-retail-recommendations';
import './module/helret-cms/blocks/commerce/helloretail';

import './module/sw-cms';

if (module.hot) {
    module.hot.accept();
}
