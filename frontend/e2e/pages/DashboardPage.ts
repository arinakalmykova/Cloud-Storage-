import { Page, Locator } from '@playwright/test';

export class DashboardPage {
  private readonly welcomeMessage: Locator;
  private readonly photoCount: Locator;
  private readonly storageUsed: Locator;
  private readonly uploadSpeed: Locator;
  private readonly navLinks: {
    upload: Locator;
    archive: Locator;
    search: Locator;
  };

  constructor(private readonly page: Page) {
    this.welcomeMessage = page.locator('h1:has-text("Панель управления")');
    this.photoCount = page.locator('.dashboardStatsNumber').first();
    this.storageUsed = page.locator('.dashboardStatsNumber').nth(1);
    this.uploadSpeed = page.locator('.dashboardStatsNumber').nth(2);
    
    this.navLinks = {
      upload: page.getByRole('link', { name: /загрузка/i }),
      archive: page.getByRole('link', { name: /архив/i }),
      search: page.getByRole('link', { name: /поиск/i })
    };
  }

  async getWelcomeText() {
    return this.welcomeMessage.textContent();
  }

  async getPhotoCount() {
    return this.photoCount.textContent();
  }

  async getStorageUsed() {
    return this.storageUsed.textContent();
  }

  async isDashboardLoaded() {
    return this.welcomeMessage.isVisible();
  }

  async navigateToUpload() {
    await this.navLinks.upload.click();
  }

  async navigateToArchive() {
    await this.navLinks.archive.click();
  }

  async navigateToSearch() {
    await this.navLinks.search.click();
  }
}