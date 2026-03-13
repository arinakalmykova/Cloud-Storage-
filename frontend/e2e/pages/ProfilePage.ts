import { Page, Locator } from '@playwright/test';

export class ProfilePage {
    // Локаторы (приватные)
    private readonly pageTitle: Locator;
    private readonly userName: Locator;
    private readonly userEmail: Locator;
    private readonly registrationDate: Locator;
    private readonly editButton: Locator;
    private readonly deleteButton: Locator;
    private readonly logoutButton: Locator;
    private readonly saveButton: Locator;
    private readonly nameInput: Locator;
    private readonly emailInput: Locator;
    private readonly formError: Locator;

    constructor(private readonly page: Page) {
        this.pageTitle = page.getByRole('heading', { name: 'Профиль' });
        this.userName = page.locator('h3:has-text("Имя:")');
        this.userEmail = page.locator('h3:has-text("Email:")');
        this.registrationDate = page.locator('h3:has-text("Дата регистрации:")');
        this.editButton = page.getByRole('button', { name: 'Редактировать' }).first();
        this.deleteButton = page.getByRole('button', { name: 'Удалить аккаунт' });
        this.logoutButton = page.getByRole('button', { name: 'Выйти' });
        this.saveButton = page.getByRole('button', { name: 'Сохранить' });
        this.nameInput = page.getByLabel('Имя:');
        this.emailInput = page.getByLabel('Email:');
        this.formError = page.locator('.formError');
    }

    // Публичные методы для проверки загрузки
    async isProfilePageLoaded(): Promise<boolean> {
        try {
            await this.page.waitForSelector('h1:has-text("Профиль")', { timeout: 5000 });
            return true;
        } catch {
            return false;
        }
    }

    // Публичные методы для получения данных
    async getUserName(): Promise<string> {
        const text = await this.userName.textContent();
        return text?.replace('Имя:', '').trim() || '';
    }

    async getUserEmail(): Promise<string> {
        const text = await this.userEmail.textContent();
        return text?.replace('Email:', '').trim() || '';
    }

    async getRegistrationDate(): Promise<string> {
        const text = await this.registrationDate.textContent();
        return text?.replace('Дата регистрации:', '').trim() || '';
    }

    // Публичные методы для действий с кнопками
    async clickEditButton(): Promise<void> {
        await this.editButton.click();
        await this.nameInput.waitFor({ state: 'visible', timeout: 5000 });
    }

    async clickSaveButton(): Promise<void> {
        await this.saveButton.click();
        await this.nameInput.waitFor({ state: 'hidden', timeout: 5000 });
    }

    async clickLogoutButton(): Promise<void> {
        await this.logoutButton.click();
    }

    async clickDeleteButton(): Promise<void> {
        await this.deleteButton.click();
    }

    // Публичные методы для работы с формой
    async fillName(name: string): Promise<void> {
        await this.nameInput.fill(name);
    }

    async fillEmail(email: string): Promise<void> {
        await this.emailInput.fill(email);
    }

    async fillEditForm(name: string, email: string): Promise<void> {
        await this.fillName(name);
        await this.fillEmail(email);
    }

    async updateProfile(name: string, email: string): Promise<void> {
        await this.clickEditButton();
        await this.fillEditForm(name, email);
        await this.clickSaveButton();
    }

    // Публичные методы для проверки ошибок
    async hasFormError(): Promise<boolean> {
        return this.formError.isVisible();
    }

    async getFormError(): Promise<string> {
        return (await this.formError.textContent()) || '';
    }

    // Публичные методы для работы с диалогами
    async confirmDelete(): Promise<void> {
        this.page.on('dialog', async dialog => {
            console.log(`Dialog message: ${dialog.message()}`);
            await dialog.accept();
        });
    }

    async cancelDelete(): Promise<void> {
        this.page.on('dialog', async dialog => {
            console.log(`Dialog message: ${dialog.message()}`);
            await dialog.dismiss();
        });
    }

    // Публичный метод для ожидания редиректа
    async waitForLogout(): Promise<void> {
        await this.page.waitForURL('/auth', { timeout: 5000 });
    }

    // Публичный метод для очистки полей
    async clearEditForm(): Promise<void> {
        await this.nameInput.clear();
        await this.emailInput.clear();
    }
}