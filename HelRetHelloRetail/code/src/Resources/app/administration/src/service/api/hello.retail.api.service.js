const ApiService = Shopware.Classes.ApiService;

class HelloRetailApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'wexo/hello-retail') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'helloRetailService';
    }

    getTypeId() {
        return '44f7e183909376bb5824abf830f4b879';
    }

    generateFeed(salesChannelId, feed) {
        return this.httpClient.post(
            `/${this.getApiBasePath()}/generateFeed/${salesChannelId}/${feed}`,
            {},
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default HelloRetailApiService;
