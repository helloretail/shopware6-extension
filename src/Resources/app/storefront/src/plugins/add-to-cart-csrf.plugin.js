import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class AddToCartCSRFPlugin extends Plugin {
    init() {
        let _awev = (window._awev || []);
        _awev.push(["bind", "context_ready", this.callback]);
    }

    callback() {
        const httpClient = new HttpClient();
        httpClient.fetchCsrfToken((token) => {
            $('.addwish-product').each(function () {
                $(this).find('input[name=_CSRF_token]').val(token);
            });
        });
    }
}
