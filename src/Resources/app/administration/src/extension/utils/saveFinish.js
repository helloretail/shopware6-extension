export default function saveFinish() {
    // Ensure saveFinish runs first, to allow vue to change page
    this.$super("saveFinish");

    /* stop if not is Hello Retail channel */
    if (this.helloRetailService.getTypeId() !== this.salesChannel.typeId) {
        return;
    }

    /* Get feeds */
    const feeds = JSON.parse(JSON.stringify(this.salesChannel.configuration.feeds));
    const salesChannelId = this.salesChannel.id;

    if (!Object.keys(feeds).length) {
        return;
    }

    this.createNotificationInfo({
        message: this.$tc("helret-hello-retail.save.info", 0, {
            feedCount: Object.keys(feeds).length
        })
    });

    /* Generate feeds based on objects keys e.g. order and product */
    Object.keys(feeds).map(feed => {
        this.helloRetailService.generateFeed(salesChannelId, feed)
            .then(response => {
                if (response.error) {
                    this.createNotificationError({
                        message: response.message
                    });
                } else {
                    this.createNotificationSuccess({
                        message: response.message
                    });
                }
            });
    });
}
