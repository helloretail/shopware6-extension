import './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/export-templates';
Shopware.Component.override('sw-admin-menu-item',() => import('./app/component/structure/sw-admin-menu-item/index'));
Shopware.Component.override('sw-sales-channel-modal-grid',() => import('./app/component/structure/sw-sales-channel-modal-grid/index'));
import './module/sw-sales-channel';

import './module/helret-cms/blocks/hello-retail/helloretail';
import './module/helret-cms/blocks/hello-retail/hello-retail-recommendations';
import './module/helret-cms/elements/hello-retail-recommendations';
import './module/helret-cms/blocks/commerce/helloretail';

import './module/sw-cms';
