// Steam API Integration Service
import axios from 'axios'

class SteamService {
  constructor() {
    this.apiKey = import.meta.env.VITE_STEAM_API_KEY
    this.baseURL = 'https://api.steampowered.com'
    this.backendURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  }

  // Steam OAuth Login
  async initiateLogin() {
    const params = new URLSearchParams({
      'openid.ns': 'http://specs.openid.net/auth/2.0',
      'openid.mode': 'checkid_setup',
      'openid.return_to': `${window.location.origin}/auth/steam/callback`,
      'openid.realm': window.location.origin,
      'openid.identity': 'http://specs.openid.net/auth/2.0/identifier_select',
      'openid.claimed_id': 'http://specs.openid.net/auth/2.0/identifier_select'
    })

    window.location.href = `https://steamcommunity.com/openid/login?${params.toString()}`
  }

  // Verify Steam Login Response
  async verifyLogin(openidParams) {
    try {
      const response = await axios.post(`${this.backendURL}/auth/steam/verify`, {
        openid_params: openidParams
      })
      return response.data
    } catch (error) {
      console.error('Steam login verification failed:', error)
      throw error
    }
  }

  // Get User Steam Profile
  async getUserProfile(steamId) {
    try {
      const response = await axios.get(`${this.backendURL}/steam/profile/${steamId}`)
      return response.data
    } catch (error) {
      console.error('Failed to fetch Steam profile:', error)
      throw error
    }
  }

  // Get User Steam Inventory
  async getUserInventory(steamId, appId = 730) {
    try {
      const response = await axios.get(`${this.backendURL}/steam/inventory/${steamId}/${appId}`)
      return response.data
    } catch (error) {
      console.error('Failed to fetch Steam inventory:', error)
      throw error
    }
  }

  // Sync User Inventory
  async syncInventory(steamId) {
    try {
      const response = await axios.post(`${this.backendURL}/steam/sync-inventory`, {
        steam_id: steamId
      })
      return response.data
    } catch (error) {
      console.error('Failed to sync inventory:', error)
      throw error
    }
  }

  // Get Market Prices
  async getMarketPrices(itemNames) {
    try {
      const response = await axios.post(`${this.backendURL}/steam/market-prices`, {
        items: itemNames
      })
      return response.data
    } catch (error) {
      console.error('Failed to fetch market prices:', error)
      throw error
    }
  }

  // Create Trade Offer
  async createTradeOffer(tradeData) {
    try {
      const response = await axios.post(`${this.backendURL}/steam/trade-offer`, tradeData)
      return response.data
    } catch (error) {
      console.error('Failed to create trade offer:', error)
      throw error
    }
  }

  // Get Trade Offer Status
  async getTradeOfferStatus(tradeOfferId) {
    try {
      const response = await axios.get(`${this.backendURL}/steam/trade-offer/${tradeOfferId}`)
      return response.data
    } catch (error) {
      console.error('Failed to get trade offer status:', error)
      throw error
    }
  }

  // Validate Item Ownership
  async validateItemOwnership(steamId, itemAssetId) {
    try {
      const response = await axios.post(`${this.backendURL}/steam/validate-item`, {
        steam_id: steamId,
        asset_id: itemAssetId
      })
      return response.data
    } catch (error) {
      console.error('Failed to validate item ownership:', error)
      throw error
    }
  }

  // Get Steam Market Item Details
  async getMarketItemDetails(marketHashName) {
    try {
      const response = await axios.get(`${this.backendURL}/steam/market-item/${encodeURIComponent(marketHashName)}`)
      return response.data
    } catch (error) {
      console.error('Failed to get market item details:', error)
      throw error
    }
  }

  // Check Steam API Status
  async checkAPIStatus() {
    try {
      const response = await axios.get(`${this.backendURL}/steam/status`)
      return response.data
    } catch (error) {
      console.error('Steam API status check failed:', error)
      return { status: 'error', message: 'Steam API unavailable' }
    }
  }

  // Format Steam ID
  formatSteamId(steamId) {
    // Convert SteamID64 to different formats if needed
    const steamId64 = BigInt(steamId)
    const steamId32 = Number(steamId64 - BigInt('76561197960265728'))
    
    return {
      steamId64: steamId.toString(),
      steamId32: steamId32,
      steamId3: `[U:1:${steamId32}]`,
      profileUrl: `https://steamcommunity.com/profiles/${steamId}`
    }
  }

  // Parse Item Float Value
  parseFloatValue(inspectLink) {
    // Extract float value from inspect link if available
    const match = inspectLink.match(/D(\d+)/)
    if (match) {
      const d = parseInt(match[1])
      return d / Math.pow(2, 32)
    }
    return null
  }

  // Calculate Item Rarity
  calculateRarity(item) {
    const rarityMap = {
      'Consumer Grade': 'consumer',
      'Industrial Grade': 'industrial',
      'Mil-Spec Grade': 'milspec',
      'Restricted': 'restricted',
      'Classified': 'classified',
      'Covert': 'covert',
      'Contraband': 'contraband'
    }

    return rarityMap[item.tags?.find(tag => tag.category === 'Rarity')?.name] || 'consumer'
  }

  // Get Item Condition from Float
  getConditionFromFloat(floatValue) {
    if (floatValue < 0.07) return 'Factory New'
    if (floatValue < 0.15) return 'Minimal Wear'
    if (floatValue < 0.38) return 'Field-Tested'
    if (floatValue < 0.45) return 'Well-Worn'
    return 'Battle-Scarred'
  }

  // Generate Item Screenshot
  async generateScreenshot(inspectLink) {
    try {
      const response = await axios.post(`${this.backendURL}/steam/screenshot`, {
        inspect_link: inspectLink
      })
      return response.data
    } catch (error) {
      console.error('Failed to generate screenshot:', error)
      throw error
    }
  }
}

export default new SteamService()
