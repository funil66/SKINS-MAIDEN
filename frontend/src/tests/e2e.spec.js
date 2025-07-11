// E2E Tests for Iron Code Skins Platform
import { test, expect } from '@playwright/test'

test.describe('Iron Code Skins - Complete E2E Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    // Setup test environment
    await page.goto('http://localhost:3000')
    await page.waitForLoadState('networkidle')
  })

  test.describe('Authentication Flow', () => {
    test('should complete Steam OAuth login', async ({ page }) => {
      await page.click('[data-testid="login-button"]')
      await page.click('[data-testid="steam-login"]')
      
      // Mock Steam OAuth
      await page.route('**/auth/steam/callback', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            success: true,
            user: {
              id: 1,
              name: 'TestUser',
              steam_id: '76561198000000001',
              avatar: '/test-avatar.jpg'
            },
            token: 'test-jwt-token'
          })
        })
      })

      await expect(page.locator('[data-testid="user-menu"]')).toBeVisible()
      await expect(page.locator('text=TestUser')).toBeVisible()
    })

    test('should handle Steam login failure', async ({ page }) => {
      await page.click('[data-testid="login-button"]')
      await page.click('[data-testid="steam-login"]')
      
      await page.route('**/auth/steam/callback', (route) => {
        route.fulfill({
          status: 400,
          body: JSON.stringify({
            success: false,
            error: 'Steam authentication failed'
          })
        })
      })

      await expect(page.locator('[data-testid="error-message"]')).toBeVisible()
      await expect(page.locator('text=Steam authentication failed')).toBeVisible()
    })
  })

  test.describe('Marketplace Functionality', () => {
    test.beforeEach(async ({ page }) => {
      // Mock authentication
      await page.addInitScript(() => {
        localStorage.setItem('auth_token', 'test-jwt-token')
        localStorage.setItem('user', JSON.stringify({
          id: 1,
          name: 'TestUser',
          steam_id: '76561198000000001'
        }))
      })
    })

    test('should display marketplace items', async ({ page }) => {
      await page.goto('/marketplace')
      
      await page.route('**/api/marketplace/items*', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            items: [
              {
                id: 1,
                name: 'AK-47 | Redline',
                price: 150.00,
                condition: 'Field-Tested',
                float: 0.25,
                image: '/test-ak47.jpg',
                rarity: 'classified'
              }
            ],
            total: 1,
            pages: 1
          })
        })
      })

      await expect(page.locator('[data-testid="marketplace-item"]')).toHaveCount(1)
      await expect(page.locator('text=AK-47 | Redline')).toBeVisible()
      await expect(page.locator('text=R$ 150,00')).toBeVisible()
    })

    test('should filter items by price range', async ({ page }) => {
      await page.goto('/marketplace')
      
      await page.fill('[data-testid="min-price"]', '100')
      await page.fill('[data-testid="max-price"]', '200')
      await page.click('[data-testid="apply-filters"]')

      await expect(page).toHaveURL(/min_price=100/)
      await expect(page).toHaveURL(/max_price=200/)
    })

    test('should search for items', async ({ page }) => {
      await page.goto('/marketplace')
      
      await page.fill('[data-testid="search-input"]', 'AK-47')
      await page.press('[data-testid="search-input"]', 'Enter')

      await expect(page).toHaveURL(/search=AK-47/)
    })
  })

  test.describe('Item Details and Purchase', () => {
    test('should display item details', async ({ page }) => {
      await page.goto('/marketplace/item/1')
      
      await page.route('**/api/marketplace/item/1', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            id: 1,
            name: 'AK-47 | Redline',
            price: 150.00,
            condition: 'Field-Tested',
            float: 0.25,
            image: '/test-ak47.jpg',
            rarity: 'classified',
            description: 'Test item description',
            seller: {
              name: 'TestSeller',
              rating: 4.8,
              reviews: 234
            }
          })
        })
      })

      await expect(page.locator('h1')).toContainText('AK-47 | Redline')
      await expect(page.locator('[data-testid="item-price"]')).toContainText('R$ 150,00')
      await expect(page.locator('[data-testid="item-condition"]')).toContainText('Field-Tested')
    })

    test('should add item to cart', async ({ page }) => {
      await page.goto('/marketplace/item/1')
      
      await page.click('[data-testid="add-to-cart"]')
      
      await expect(page.locator('[data-testid="cart-notification"]')).toContainText('Item adicionado ao carrinho')
      await expect(page.locator('[data-testid="cart-count"]')).toContainText('1')
    })
  })

  test.describe('Shopping Cart', () => {
    test('should display cart items', async ({ page }) => {
      // Add item to cart first
      await page.goto('/marketplace/item/1')
      await page.click('[data-testid="add-to-cart"]')
      
      await page.goto('/cart')
      
      await expect(page.locator('[data-testid="cart-item"]')).toHaveCount(1)
      await expect(page.locator('[data-testid="subtotal"]')).toBeVisible()
      await expect(page.locator('[data-testid="service-fee"]')).toBeVisible()
      await expect(page.locator('[data-testid="total"]')).toBeVisible()
    })

    test('should remove item from cart', async ({ page }) => {
      await page.goto('/cart')
      
      await page.click('[data-testid="remove-item"]')
      
      await expect(page.locator('[data-testid="cart-item"]')).toHaveCount(0)
      await expect(page.locator('text=Seu carrinho está vazio')).toBeVisible()
    })
  })

  test.describe('Checkout Process', () => {
    test('should complete PIX checkout', async ({ page }) => {
      await page.goto('/checkout')
      
      await page.check('[data-testid="payment-pix"]')
      await page.check('[data-testid="agree-terms"]')
      
      await page.route('**/api/payments/pix/create', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            pix_code: 'test-pix-code',
            qr_code: 'data:image/png;base64,test-qr',
            transaction_id: 'test-tx-123'
          })
        })
      })
      
      await page.click('[data-testid="confirm-purchase"]')
      
      await expect(page.locator('[data-testid="pix-qr-code"]')).toBeVisible()
      await expect(page.locator('[data-testid="pix-code"]')).toBeVisible()
    })

    test('should complete credit card checkout', async ({ page }) => {
      await page.goto('/checkout')
      
      await page.check('[data-testid="payment-card"]')
      await page.fill('[data-testid="card-number"]', '4111111111111111')
      await page.fill('[data-testid="card-holder"]', 'Test User')
      await page.fill('[data-testid="expiry-month"]', '12')
      await page.fill('[data-testid="expiry-year"]', '2030')
      await page.fill('[data-testid="cvv"]', '123')
      await page.check('[data-testid="agree-terms"]')
      
      await page.route('**/api/payments/card/create', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            transaction_id: 'test-card-tx-123',
            status: 'approved',
            authorization_code: 'AUTH123'
          })
        })
      })
      
      await page.click('[data-testid="confirm-purchase"]')
      
      await expect(page).toHaveURL(/\/purchase\/confirmation/)
    })
  })

  test.describe('Trading System', () => {
    test('should display trading center', async ({ page }) => {
      await page.goto('/trading')
      
      await page.route('**/api/trades*', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            trades: [
              {
                id: 1,
                status: 'pending',
                initiator: { name: 'TraderA', avatar: '/avatar1.jpg' },
                target: { name: 'TraderB', avatar: '/avatar2.jpg' },
                initiator_items: [],
                target_items: [],
                created_at: new Date().toISOString()
              }
            ]
          })
        })
      })

      await expect(page.locator('[data-testid="trade-item"]')).toHaveCount(1)
      await expect(page.locator('text=TraderA')).toBeVisible()
    })

    test('should create new trade', async ({ page }) => {
      await page.goto('/trading')
      
      await page.click('[data-testid="new-trade-button"]')
      
      await expect(page).toHaveURL(/\/trading\/new/)
    })
  })

  test.describe('User Dashboard', () => {
    test('should display user statistics', async ({ page }) => {
      await page.goto('/dashboard')
      
      await page.route('**/api/user/stats', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            total_purchases: 15,
            total_sales: 8,
            total_trades: 23,
            wallet_balance: 1250.00
          })
        })
      })

      await expect(page.locator('[data-testid="stat-purchases"]')).toContainText('15')
      await expect(page.locator('[data-testid="stat-sales"]')).toContainText('8')
      await expect(page.locator('[data-testid="stat-trades"]')).toContainText('23')
      await expect(page.locator('[data-testid="wallet-balance"]')).toContainText('R$ 1.250,00')
    })

    test('should display recent activity', async ({ page }) => {
      await page.goto('/dashboard')
      
      await page.route('**/api/user/activity', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            activities: [
              {
                id: 1,
                type: 'purchase',
                description: 'Purchased AK-47 | Redline',
                timestamp: new Date().toISOString()
              }
            ]
          })
        })
      })

      await expect(page.locator('[data-testid="activity-item"]')).toHaveCount(1)
      await expect(page.locator('text=Purchased AK-47 | Redline')).toBeVisible()
    })
  })

  test.describe('Admin Panel', () => {
    test.beforeEach(async ({ page }) => {
      // Mock admin authentication
      await page.addInitScript(() => {
        localStorage.setItem('auth_token', 'admin-jwt-token')
        localStorage.setItem('user', JSON.stringify({
          id: 1,
          name: 'AdminUser',
          role: 'admin'
        }))
      })
    })

    test('should display admin dashboard', async ({ page }) => {
      await page.goto('/admin')
      
      await page.route('**/api/admin/stats', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            total_users: 15420,
            total_revenue: 487320.50,
            total_skins: 89651,
            active_trades: 1247
          })
        })
      })

      await expect(page.locator('[data-testid="admin-stat-users"]')).toContainText('15,420')
      await expect(page.locator('[data-testid="admin-stat-revenue"]')).toContainText('487,320.50')
    })

    test('should manage users', async ({ page }) => {
      await page.goto('/admin')
      await page.click('[data-testid="users-nav"]')
      
      await page.route('**/api/admin/users*', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            users: [
              {
                id: 1,
                name: 'TestUser',
                email: 'test@example.com',
                status: 'active',
                created_at: new Date().toISOString()
              }
            ]
          })
        })
      })

      await expect(page.locator('[data-testid="user-row"]')).toHaveCount(1)
      await expect(page.locator('text=TestUser')).toBeVisible()
    })
  })

  test.describe('Performance Tests', () => {
    test('should load marketplace within performance budget', async ({ page }) => {
      const startTime = Date.now()
      await page.goto('/marketplace')
      await page.waitForLoadState('networkidle')
      const loadTime = Date.now() - startTime
      
      expect(loadTime).toBeLessThan(3000) // 3 seconds
    })

    test('should handle large item lists efficiently', async ({ page }) => {
      await page.goto('/marketplace')
      
      // Mock large dataset
      const largeItemList = Array.from({ length: 1000 }, (_, i) => ({
        id: i + 1,
        name: `Test Item ${i + 1}`,
        price: Math.random() * 1000,
        condition: 'Factory New',
        image: '/test-item.jpg'
      }))

      await page.route('**/api/marketplace/items*', (route) => {
        route.fulfill({
          status: 200,
          body: JSON.stringify({
            items: largeItemList.slice(0, 20),
            total: 1000,
            pages: 50
          })
        })
      })

      await expect(page.locator('[data-testid="marketplace-item"]')).toHaveCount(20)
      
      // Test pagination
      await page.click('[data-testid="next-page"]')
      await expect(page).toHaveURL(/page=2/)
    })
  })

  test.describe('Security Tests', () => {
    test('should prevent XSS attacks', async ({ page }) => {
      await page.goto('/marketplace')
      
      const maliciousScript = '<script>alert("XSS")</script>'
      await page.fill('[data-testid="search-input"]', maliciousScript)
      await page.press('[data-testid="search-input"]', 'Enter')
      
      // Should not execute the script
      const alerts = []
      page.on('dialog', dialog => {
        alerts.push(dialog.message())
        dialog.dismiss()
      })
      
      await page.waitForTimeout(1000)
      expect(alerts).toHaveLength(0)
    })

    test('should validate CSRF protection', async ({ page }) => {
      await page.goto('/marketplace/item/1')
      
      // Try to make request without CSRF token
      const response = await page.request.post('/api/cart/add', {
        data: { item_id: 1 }
      })
      
      expect(response.status()).toBe(419) // CSRF token mismatch
    })
  })

  test.describe('Mobile Responsiveness', () => {
    test('should work on mobile viewport', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 })
      await page.goto('/marketplace')
      
      await expect(page.locator('[data-testid="mobile-menu-button"]')).toBeVisible()
      await expect(page.locator('[data-testid="marketplace-item"]')).toBeVisible()
    })

    test('should have working mobile navigation', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 })
      await page.goto('/')
      
      await page.click('[data-testid="mobile-menu-button"]')
      await expect(page.locator('[data-testid="mobile-nav"]')).toBeVisible()
      
      await page.click('[data-testid="nav-marketplace"]')
      await expect(page).toHaveURL(/\/marketplace/)
    })
  })

  test.describe('PWA Functionality', () => {
    test('should register service worker', async ({ page }) => {
      await page.goto('/')
      
      const swRegistration = await page.evaluate(() => {
        return navigator.serviceWorker.ready
      })
      
      expect(swRegistration).toBeTruthy()
    })

    test('should work offline for cached pages', async ({ page, context }) => {
      await page.goto('/')
      await page.waitForLoadState('networkidle')
      
      // Go offline
      await context.setOffline(true)
      
      await page.reload()
      await expect(page.locator('body')).toBeVisible()
    })
  })
})

test.describe('Load Testing Simulation', () => {
  test('should handle concurrent users', async ({ page }) => {
    const promises = []
    
    // Simulate 10 concurrent users
    for (let i = 0; i < 10; i++) {
      promises.push(
        page.goto('/marketplace').then(() => {
          return page.waitForLoadState('networkidle')
        })
      )
    }
    
    const results = await Promise.allSettled(promises)
    const successful = results.filter(r => r.status === 'fulfilled').length
    
    expect(successful).toBeGreaterThanOrEqual(8) // 80% success rate
  })
})
