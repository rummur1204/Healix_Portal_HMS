import { computed } from 'vue'

export function useColors() {
    const colors = {
        primary: {
            50: '#f0fdf4', // lightest green
            100: '#dcfce7',
            200: '#bbf7d0',
            300: '#86efac',
            400: '#4ade80',
            500: '#22c55e', // light green
            600: '#16a34a',
            700: '#15803d', // dark green
            800: '#166534',
            900: '#14532d', // darkest green
        },
        teal: {
            50: '#f0fdfa',
            100: '#ccfbf1',
            200: '#99f6e4',
            300: '#5eead4',
            400: '#2dd4bf',
            500: '#14b8a6', // teal
            600: '#0d9488',
            700: '#0f766e',
            800: '#115e59',
            900: '#134e4a',
        },
        white: {
            DEFAULT: '#ffffff',
            off: '#f9fafb',
            pure: '#ffffff',
        }
    }

    const getStatusColor = (status) => {
        const map = {
            trial: 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100',
            active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
            past_due: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
            cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
            suspended: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
            unpaid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
            partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
        }
        return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }

    const getStatusLabel = (status) => {
        const map = {
            trial: 'Trial',
            active: 'Active',
            past_due: 'Past Due',
            cancelled: 'Cancelled',
            suspended: 'Suspended',
            paid: 'Paid',
            unpaid: 'Unpaid',
            partial: 'Partial',
        }
        return map[status] || status
    }

    return {
        colors,
        getStatusColor,
        getStatusLabel,
    }
}