import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { RegisterPage } from '../pages/RegisterPage';

test.describe('Сценарии  регистрации', () => {
  
  // Сценарий 3: Регистрация нового пользователя и проверка входа
  test('Сценарий 3: Регистрация нового пользователя и успешный вход', async ({ page }) => {
    const loginPage = new LoginPage(page);
    const registerPage = new RegisterPage(page);
    
    // Генерируем уникальные данные для нового пользователя
    const timestamp = Date.now();
    const email = `user${timestamp}@test.com`;
    const password = 'Test123!';
    const name = `Test User ${timestamp}`;
    
    await page.goto('http://localhost:80/auth');
    
    // Регистрируем нового пользователя
    await registerPage.register(name, email, password);
    
    // Проверяем сообщение об успешной регистрации
    await expect(
      page.locator('text=Регистрация прошла успешно! Пожалуйста, войдите.')
    ).toBeVisible();
    
    // Переключаемся на таб логина
    await page.getByRole('button', { name: /вход/i }).click();

    // Теперь логинимся
    await loginPage.login(email, password);
    // Проверяем успешный вход
    await expect(page).toHaveURL('http://localhost:80/');
  });

});