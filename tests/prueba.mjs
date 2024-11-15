import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost/login/login.php');
  await page.getByPlaceholder('Ingresa tu usuario').click();
  await page.getByPlaceholder('Ingresa tu usuario').fill('admin');
  await page.getByPlaceholder('Ingresa tu usuario').press('Tab');
  await page.getByPlaceholder('Ingresa tu contraseña').fill('123');
  await page.getByRole('button', { name: 'Iniciar sesión' }).click();
});