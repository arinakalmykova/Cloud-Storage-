import { Page, Locator } from '@playwright/test';

export class RegisterPage {
  private readonly registerTab: Locator;
  private readonly nameInput: Locator;
  private readonly emailInput: Locator;
  private readonly passwordInput: Locator;
  private readonly confirmPasswordInput: Locator;
  private readonly registerButton: Locator;
  private readonly errorMessage: Locator;
  private readonly successMessage: Locator;

  constructor(private readonly page: Page) {
    // Таб для переключения на регистрацию
    this.registerTab = page.getByRole('button', { name: /регистрация/i });
    
    // Поля формы регистрации (появляются после клика на таб)
    this.nameInput = page.locator('#name');
    this.emailInput = page.locator('#email').last();
    this.passwordInput = page.locator('#password').last();
    this.confirmPasswordInput = page.locator('#confirmPassword');
    this.registerButton = page.getByRole('button', { name: /зарегистрироваться/i });
    this.errorMessage = page.locator('text=Ошибка регистрации');
    this.successMessage = page.locator('text=Регистрация прошла успешно');
  }

  async switchToRegisterTab() {
    await this.registerTab.click();
    // Ждем появления формы регистрации
    await this.nameInput.waitFor({ state: 'visible', timeout: 5000 });
  }

  async fillName(name: string) {
    await this.nameInput.fill(name);
  }

  async fillEmail(email: string) {
    await this.emailInput.fill(email);
  }

  async fillPassword(password: string) {
    await this.passwordInput.fill(password);
  }

  async fillConfirmPassword(password: string) {
    await this.confirmPasswordInput.fill(password);
  }

  async clickRegisterButton() {
    await this.registerButton.click();
  }

  async register(name: string, email: string, password: string) {
    await this.switchToRegisterTab();
    await this.fillName(name);
    await this.fillEmail(email);
    await this.fillPassword(password);
    await this.fillConfirmPassword(password);
    await this.clickRegisterButton();
  }

  async isErrorMessageVisible() {
    return this.errorMessage.isVisible();
  }

  async isSuccessMessageVisible() {
    return this.successMessage.isVisible();
  }
}