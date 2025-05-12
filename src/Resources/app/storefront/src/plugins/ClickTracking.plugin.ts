// @ts-ignore
export default class ClickTracking extends window.PluginBaseClass {
  private el: HTMLElement;
  private awSource: string;

  init() {
    if (!this.el.dataset.awSource) {
      return;
    }

    this.awSource = this.el.dataset.awSource;

    const container = this.el.closest('.card,.search-suggest-product');
    console.log(container);
    container?.querySelectorAll<HTMLAnchorElement>('a[href]').forEach((el) => {
      el.href = `${el.href}#aw_source=${this.awSource}`;
    });
  }
}