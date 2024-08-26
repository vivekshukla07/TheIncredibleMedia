/**
 * Description: ES6 class exportée pour 'eac-rss-reader', 'eac-news-ticker', 'eac-pinterest-rss'
 *
 * @since 2.1.3
 */
export default class EacReadTheFeed {
    constructor(feedUrl, nonce, id) {
        this.allItems = [];
        this.itemError = {};
        this.ajaxOption = Math.random().toString(36).substring(2, 10); // Génère un nombre aléatoire unique pour l'instance courante
        this.proxy = eacElementsPath.proxies + 'proxy-rss.php'; // eacElementsPath est initialisé dans 'eac-register-scripts.php'
        this.proxyUrl = encodeURIComponent(feedUrl);
        this.proxyNonce = nonce;
        this.proxyId = id;
        if (this.proxyUrl && this.proxyNonce && this.proxyId) {
            this.callRss();
        }
    }

    getItems() {
        return this.allItems[0];
    }

    getOptions() {
        return this.ajaxOption;
    }

    callRss() {
        const self = this;

        jQuery.ajax({
            url: this.proxy,
            type: 'GET',
            data: { url: this.proxyUrl, nonce: this.proxyNonce, id: this.proxyId },
            dataType: 'json',
            ajaxOptions: this.ajaxOption,
        }).done((data, textStatus, jqXHR) => { // les proxy echo des données 'encodées par json_encode', mais il peut être vide
            if (jqXHR.responseJSON === null) {
                self.itemError.headError = 'Nothing to display...';
                self.allItems.push(self.itemError);
                return false;
            }
            self.allItems.push(data);
        }).fail((jqXHR, textStatus) => { // Les proxy echo des données 'non encodées par json_encode'. textStatus == parseerror
            self.itemError.headError = jqXHR.responseText;
            self.allItems.push(self.itemError);
            return false;
        });
    }
}
