import { Page, Locator } from '@playwright/test';

export class LoginPage {
  private readonly emailInput: Locator;
  private readonly passwordInput: Locator;
  private readonly loginButton: Locator;
  private readonly errorMessage: Locator;

  constructor(private readonly page: Page) {
    // Используем id из React компонента
    this.emailInput = page.locator('#email');
    this.passwordInput = page.locator('#password');
    this.loginButton = page.getByRole('button', { name: /войти/i });
    this.errorMessage = page.locator('text=Ошибка входа');
  }

  async fillEmail(email: string) {
    await this.emailInput.fill(email);
  }

  async fillPassword(password: string) {
    await this.passwordInput.fill(password);
  }

  async clickLoginButton() {
    await this.loginButton.click();
  }

  async login(email: string, password: string) {
    await this.fillEmail(email);
    await this.fillPassword(password);
    await this.clickLoginButton();
  }


  async isLoginPageLoaded() {
    // Проверяем наличие формы по id
    return this.page.locator('#email').isVisible();
  }

  async isErrorMessageVisible() {
  return this.page.locator('text=Ошибка входа').isVisible();
}
}