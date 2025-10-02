import { QueryClient } from '@tanstack/vue-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes
      gcTime: 10 * 60 * 1000, // 10 minutes (formerly cacheTime)
      retry: (failureCount, error: any) => {
        // Don't retry on 4xx errors except 408, 429
        if (error?.response?.status >= 400 && error?.response?.status < 500) {
          if (
            error?.response?.status === 408 ||
            error?.response?.status === 429
          ) {
            return failureCount < 3;
          }
          return false;
        }
        // Retry on 5xx errors and network errors
        return failureCount < 3;
      },
      retryDelay: attemptIndex => Math.min(1000 * 2 ** attemptIndex, 30000),
    },
    mutations: {
      retry: false, // Don't retry mutations by default
    },
  },
});

// Global error handler
queryClient.setMutationDefaults(['residents'], {
  onError: (error: any) => {
    console.error('Resident mutation error:', error);
    // You can add global error handling here (e.g., toast notifications)
  },
});

export default queryClient;
