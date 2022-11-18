import './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/init/svg-icons.init';
import './app/export-templates';

import './module/sw-sales-channel';


import './module/helret-cms/blocks/commerce/helloretail';


if (module.hot) {
    module.hot.accept();
}
