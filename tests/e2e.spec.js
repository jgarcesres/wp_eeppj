const { test, expect } = require('@playwright/test');

test.describe('PQRRS Form', () => {
  test('form renders all required fields', async ({ page }) => {
    await page.goto('/pqrrs/');

    await expect(page.locator('input[name="nombre"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('select[name="tipo"]')).toBeVisible();
    await expect(page.locator('input[name="asunto"]')).toBeVisible();
    await expect(page.locator('textarea[name="mensaje"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('form shows validation errors on empty submit', async ({ page }) => {
    await page.goto('/pqrrs/');

    // Click submit without filling anything
    await page.locator('#pqrrs-submit').click();

    // Should stay on the same page (AJAX, no redirect)
    expect(page.url()).toContain('/pqrrs/');

    // Field error messages should appear
    const errorEls = page.locator('.pqrrs-field-error.visible');
    await expect(errorEls.first()).toBeVisible();
  });

  test('form submits successfully with valid data', async ({ page }) => {
    await page.goto('/pqrrs/');

    await page.fill('input[name="nombre"]', 'Juan Pérez Prueba');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.selectOption('select[name="tipo"]', 'peticion');
    await page.fill('input[name="asunto"]', 'Solicitud de prueba CI');
    await page.fill('textarea[name="mensaje"]', 'Este es un mensaje de prueba enviado desde el pipeline de CI para validar el formulario.');

    await page.locator('#pqrrs-submit').click();

    // Wait for AJAX response — should show success status
    const status = page.locator('#pqrrs-status');
    await expect(status).toBeVisible({ timeout: 10000 });
    await expect(status).toHaveClass(/success/);
  });
});

test.describe('Navigation', () => {
  test('header nav has key links', async ({ page }) => {
    await page.goto('/');
    const header = page.locator('header');
    await expect(header).toBeVisible();
  });

  test('footer renders with company info', async ({ page }) => {
    await page.goto('/');
    const footer = page.locator('footer');
    await expect(footer).toBeVisible();
  });
});

test.describe('Search', () => {
  test('search returns results for known content', async ({ page }) => {
    await page.goto('/?s=prueba');
    await expect(page.locator('body')).toContainText('prueba');
  });
});
