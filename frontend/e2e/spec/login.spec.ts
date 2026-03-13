import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';

test.describe('Сценарии авторизации и регистрации', () => {
  
  // Сценарий 1: Позитивный - успешная авторизация существующего пользователя
 test('Сценарий 1: Успешная авторизация существующего пользователя', async ({ page }) => {
  const loginPage = new LoginPage(page);

  await page.goto('http://localhost:80/auth');

  const responsePromise = page.waitForResponse(
    res => res.url().includes('/login') && res.request().method() === 'POST'
  );

  await loginPage.login('aricrate@gmail.com', '111111');

  const response = await responsePromise;
  expect(response.status()).toBe(200);

  await expect(page).toHaveURL('http://localhost:80/');
});

  // Сценарий 2: Негативный - вход с неверным паролем
test('Сценарий 2: Вход с неверным паролем показывает ошибку', async ({ page }) => {
  const loginPage = new LoginPage(page);

  await page.goto('http://localhost:80/auth');

  const [response] = await Promise.all([
    page.waitForResponse(
      res => res.url().includes('/login') && res.request().method() === 'POST'
    ),
    loginPage.login('aricrate@gmail.com', 'wrongpassword123')
  ]);


  await expect(page.getByText(/Ошибка входа/i)).toBeVisible();
  await expect(page).toHaveURL('http://localhost:80/auth');
});

});