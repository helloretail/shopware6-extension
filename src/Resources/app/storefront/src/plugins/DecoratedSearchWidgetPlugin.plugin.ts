// @ts-ignore
export default class DecoratedSearchWidgetPlugin extends window.PluginBaseClass {
  private el: HTMLElement;

  init() {
    this.el.addEventListener('afterSuggest', () => {
      // @ts-ignore
      window.PluginManager.initializePlugin('aw-click-tracking', '.search-suggest-product [data-aw-source]');
    });
  }
}