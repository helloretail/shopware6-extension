// @ts-ignore
export default class ClickTracking extends window.PluginBaseClass {
  private el: HTMLElement;
  private awSource: string;

  init() {
    if (!this.el.dataset.awSource) {
      return;
    }

    this.awSource = this.el.dataset.awSource;

    this.el.closest('.card')
      .querySelectorAll<HTMLAnchorElement>('a[href]').forEach((el) => {
        el.href = `${el.href}#aw_source=${this.awSource}`;
      });
  }
}