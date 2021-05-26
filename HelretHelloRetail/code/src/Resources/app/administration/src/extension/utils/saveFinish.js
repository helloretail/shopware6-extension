export default function saveFinish() {
    /* stop if not is Hello Retail channel */
    if (this.helloRetailService.getTypeId() !== this.salesChannel.typeId) {
        return;
    }
    /* Get feeds */
    const feeds = JSON.parse(JSON.stringify(this.salesChannel.configuration.feeds));
    const salesChannelId = this.salesChannel.id;
    const helretService = this.helloRetailService;
    /* Generate feeds based on objects keys eg order and product */

    Promise.all(
        Object.keys(feeds).map(feed => helretService.generateFeed(salesChannelId, feed))
    ).then( data => {
        data.forEach(response => {

            if (response.error) {
                this.createNotificationError({
                    title: this.$t('Error'),
                    message: response.message
                });
            } else {
                this.createNotificationSuccess({
                    title: this.$t('Success'),
                    message: response.message
                })
            }
        })
        this.$super("saveFinish");
    })
}
