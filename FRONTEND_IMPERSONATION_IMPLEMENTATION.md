# Frontend Impersonation Implementation Guide

## Overview

This document outlines the implementation requirements for supporting admin impersonation in the React frontend. When a super-admin impersonates a regular user from the backend, they are redirected to the frontend with a special token that should automatically authenticate them as that user.

## Backend Implementation

### Impersonation Flow

1. **Super-admin clicks "Impersonate"** on a user in the backend admin panel
2. **Backend generates a Sanctum token** for the user (24-hour expiration)
3. **Backend redirects to frontend** with token in query parameter: `/dashboard?impersonation_token={token}`
4. **Frontend detects the token** and automatically authenticates the user
5. **Frontend shows impersonation banner** indicating admin is viewing as user
6. **Admin can stop impersonation** via banner button, which redirects back to backend

### API Endpoint

**GET `/api/user`** (existing endpoint)
- Returns current authenticated user
- When called with impersonation token, returns the impersonated user's data
- Should include `is_impersonated: true` flag in response

**POST `/api/impersonate/stop`** (new endpoint needed)
- Revokes the impersonation token
- Returns redirect URL to backend admin panel
- Clears frontend authentication state

## Frontend Implementation Requirements

### 1. Token Detection and Authentication

When the frontend loads with `?impersonation_token={token}` in the URL:

```typescript
// utils/impersonation.ts
export const handleImpersonationToken = async (token: string) => {
  // Store token in localStorage
  localStorage.setItem('auth_token', token);
  localStorage.setItem('is_impersonated', 'true');
  
  // Fetch user data to verify token
  const response = await fetch(`${API_URL}/user`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    },
  });
  
  if (response.ok) {
    const data = await response.json();
    // Update auth context with user data
    // Show impersonation banner
    // Remove token from URL
    window.history.replaceState({}, '', window.location.pathname);
    return data.user;
  } else {
    // Invalid token, redirect to login
    localStorage.removeItem('auth_token');
    localStorage.removeItem('is_impersonated');
    window.location.href = '/login';
  }
};
```

### 2. Impersonation Banner Component

Create a prominent banner at the top of the dashboard when impersonating:

```tsx
// components/ImpersonationBanner.tsx
import { useState, useEffect } from 'react';

export const ImpersonationBanner = () => {
  const [isImpersonated, setIsImpersonated] = useState(false);
  const [userName, setUserName] = useState('');

  useEffect(() => {
    const impersonated = localStorage.getItem('is_impersonated') === 'true';
    setIsImpersonated(impersonated);
    
    if (impersonated) {
      // Fetch current user to get name
      fetchUser().then(user => setUserName(user.name));
    }
  }, []);

  const handleStopImpersonation = async () => {
    const token = localStorage.getItem('auth_token');
    
    try {
      const response = await fetch(`${API_URL}/impersonate/stop`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      
      const data = await response.json();
      
      // Clear frontend auth state
      localStorage.removeItem('auth_token');
      localStorage.removeItem('is_impersonated');
      
      // Redirect to backend
      if (data.redirect_url) {
        window.location.href = data.redirect_url;
      }
    } catch (error) {
      console.error('Failed to stop impersonation:', error);
    }
  };

  if (!isImpersonated) return null;

  return (
    <div className="bg-yellow-50 border-b border-yellow-200 px-4 py-3">
      <div className="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div className="text-sm">
            <span className="font-semibold text-yellow-900">You are impersonating:</span>
            <span className="text-yellow-800 ml-2">{userName}</span>
          </div>
        </div>
        <button
          onClick={handleStopImpersonation}
          className="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
          Stop Impersonating
        </button>
      </div>
    </div>
  );
};
```

### 3. Update Auth Context/Hook

Modify your authentication hook/context to handle impersonation:

```typescript
// hooks/useAuth.ts or context/AuthContext.tsx
export const useAuth = () => {
  const [user, setUser] = useState<User | null>(null);
  const [isImpersonated, setIsImpersonated] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check for impersonation token in URL
    const urlParams = new URLSearchParams(window.location.search);
    const impersonationToken = urlParams.get('impersonation_token');
    
    if (impersonationToken) {
      handleImpersonationToken(impersonationToken).then(userData => {
        setUser(userData);
        setIsImpersonated(true);
        setLoading(false);
      });
    } else {
      // Normal auth flow
      const token = localStorage.getItem('auth_token');
      const impersonated = localStorage.getItem('is_impersonated') === 'true';
      
      if (token) {
        fetchUser(token).then(userData => {
          setUser(userData);
          setIsImpersonated(impersonated);
          setLoading(false);
        });
      } else {
        setLoading(false);
      }
    }
  }, []);

  return {
    user,
    isImpersonated,
    loading,
    // ... other auth methods
  };
};
```

### 4. Dashboard Layout Update

Add the impersonation banner to your main dashboard layout:

```tsx
// layouts/DashboardLayout.tsx
import { ImpersonationBanner } from '@/components/ImpersonationBanner';

export const DashboardLayout = ({ children }) => {
  return (
    <div className="min-h-screen bg-gray-50">
      <ImpersonationBanner />
      {/* Rest of your layout */}
      {children}
    </div>
  );
};
```

### 5. API Client Update

Ensure your API client includes the impersonation token in all requests:

```typescript
// services/api.ts
const getAuthToken = () => {
  return localStorage.getItem('auth_token');
};

const apiRequest = async (endpoint: string, options: RequestInit = {}) => {
  const token = getAuthToken();
  
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...options.headers,
  };
  
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  
  // ... rest of API request logic
};
```

## Security Considerations

1. **Token Expiration**: Impersonation tokens expire after 24 hours
2. **Token Validation**: Always validate tokens with the backend before trusting them
3. **Clear State on Logout**: Remove impersonation flags when user logs out
4. **Visual Indicator**: Always show the impersonation banner when active
5. **No Sensitive Actions**: Consider disabling certain actions (like password changes) during impersonation

## Testing Checklist

- [ ] Token in URL automatically authenticates user
- [ ] Impersonation banner appears when active
- [ ] All API requests include impersonation token
- [ ] Stop impersonation button works correctly
- [ ] Redirect to backend works after stopping
- [ ] Token expiration is handled gracefully
- [ ] Invalid tokens redirect to login
- [ ] Normal login flow still works
- [ ] Impersonation state persists on page refresh
- [ ] Impersonation state clears on logout

## Backend API Contract

### GET `/api/user`

**Response when impersonating:**
```json
{
  "success": true,
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    // ... other user fields
  },
  "is_impersonated": true,
  "impersonator": {
    "id": 1,
    "name": "Admin User"
  }
}
```

### POST `/api/impersonate/stop`

**Request Headers:**
```
Authorization: Bearer {impersonation_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Impersonation stopped",
  "redirect_url": "http://backend-url/admin/users"
}
```

## Environment Variables

Add to your frontend `.env`:

```env
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_BACKEND_URL=http://localhost:8000
```

## Implementation Priority

1. **High Priority:**
   - Token detection and authentication
   - Impersonation banner component
   - API client token handling

2. **Medium Priority:**
   - Stop impersonation endpoint integration
   - Error handling for invalid tokens
   - Token expiration handling

3. **Low Priority:**
   - Disable sensitive actions during impersonation
   - Enhanced logging/audit trail
   - Impersonation session timeout warnings

---

**Status**: ⚠️ **REQUIRES FRONTEND IMPLEMENTATION**
**Last Updated**: 2026-01-24

