const tailwindCardsPlugin = ({ addComponents, theme }) => {
    const cards = {
        '.card-basic': {
            overflow: 'hidden',
            borderRadius: theme('borderRadius.lg'),
            boxShadow: theme('boxShadow.sm'),
            transition: 'box-shadow 0.3s',
            '&:hover': {
                boxShadow: theme('boxShadow.lg'),
            },
            '& img': {
                height: '14rem',
                width: '100%',
                objectFit: 'cover',
            },
            '& .card-content': {
                backgroundColor: theme('colors.white'),
                padding: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                },
            },
            '& time': {
                display: 'block',
                fontSize: theme('fontSize.xs'),
                color: theme('colors.gray.500'),
            },
            '& h3': {
                marginTop: theme('spacing.0.5'),
                fontSize: theme('fontSize.lg'),
                color: theme('colors.gray.900'),
            },
            '& p': {
                marginTop: theme('spacing.2'),
                display: '-webkit-box',
                '-webkit-line-clamp': '3',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                lineHeight: '1.625',
                color: theme('colors.gray.500'),
            },
        },
        '.card-border': {
            overflow: 'hidden',
            borderRadius: theme('borderRadius.lg'),
            border: `1px solid ${theme('colors.gray.100')}`,
            backgroundColor: theme('colors.white'),
            boxShadow: theme('boxShadow.xs'),
            '& img': {
                height: '14rem',
                width: '100%',
                objectFit: 'cover',
            },
            '& .card-content': {
                padding: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                },
            },
            '& h3': {
                fontSize: theme('fontSize.lg'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.gray.900'),
            },
            '& p': {
                marginTop: theme('spacing.2'),
                display: '-webkit-box',
                '-webkit-line-clamp': '3',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                lineHeight: '1.625',
                color: theme('colors.gray.500'),
            },
            '& .card-link': {
                display: 'inline-flex',
                alignItems: 'center',
                gap: theme('spacing.1'),
                marginTop: theme('spacing.4'),
                fontSize: theme('fontSize.sm'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.blue.600'),
                '& span': {
                    display: 'block',
                    transition: 'margin-left 0.3s',
                },
                '&:hover span': {
                    marginLeft: theme('spacing.0.5'),
                },
            },
        },
        '.card-tags': {
            borderRadius: '10px',
            border: `1px solid ${theme('colors.gray.200')}`,
            backgroundColor: theme('colors.white'),
            padding: theme('spacing.4'),
            paddingTop: theme('spacing.12'),
            paddingBottom: theme('spacing.4'),
            '& time': {
                display: 'block',
                fontSize: theme('fontSize.xs'),
                color: theme('colors.gray.500'),
            },
            '& h3': {
                marginTop: theme('spacing.0.5'),
                fontSize: theme('fontSize.lg'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.gray.900'),
            },
            '& .card-tags-wrapper': {
                display: 'flex',
                flexWrap: 'wrap',
                gap: theme('spacing.1'),
                marginTop: theme('spacing.4'),
            },
            '& .tag': {
                borderRadius: theme('borderRadius.full'),
                backgroundColor: theme('colors.purple.100'),
                paddingLeft: theme('spacing.2.5'),
                paddingRight: theme('spacing.2.5'),
                paddingTop: theme('spacing.0.5'),
                paddingBottom: theme('spacing.0.5'),
                fontSize: theme('fontSize.xs'),
                whiteSpace: 'nowrap',
                color: theme('colors.purple.600'),
            },
        },
        '.card-icon': {
            borderRadius: theme('borderRadius.lg'),
            border: `1px solid ${theme('colors.gray.100')}`,
            backgroundColor: theme('colors.white'),
            padding: theme('spacing.4'),
            boxShadow: theme('boxShadow.xs'),
            transition: 'box-shadow 0.3s',
            '@media (min-width: 640px)': {
                padding: theme('spacing.6'),
            },
            '&:hover': {
                boxShadow: theme('boxShadow.lg'),
            },
            '& .card-icon-wrapper': {
                display: 'inline-block',
                borderRadius: theme('borderRadius.sm'),
                backgroundColor: theme('colors.blue.600'),
                padding: theme('spacing.2'),
                color: theme('colors.white'),
            },
            '& h3': {
                marginTop: theme('spacing.0.5'),
                fontSize: theme('fontSize.lg'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.gray.900'),
            },
            '& p': {
                marginTop: theme('spacing.2'),
                display: '-webkit-box',
                '-webkit-line-clamp': '3',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                lineHeight: '1.625',
                color: theme('colors.gray.500'),
            },
            '& .card-link': {
                display: 'inline-flex',
                alignItems: 'center',
                gap: theme('spacing.1'),
                marginTop: theme('spacing.4'),
                fontSize: theme('fontSize.sm'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.blue.600'),
                '& span': {
                    display: 'block',
                    transition: 'margin-left 0.3s',
                },
                '&:hover span': {
                    marginLeft: theme('spacing.0.5'),
                },
            },
        },
        '.card-horizontal': {
            display: 'flex',
            backgroundColor: theme('colors.white'),
            transition: 'box-shadow 0.3s',
            '&:hover': {
                boxShadow: theme('boxShadow.xl'),
            },
            '& .card-date': {
                transform: 'rotate(180deg)',
                padding: theme('spacing.2'),
                writingMode: 'vertical-lr',
                '& time': {
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: theme('spacing.4'),
                    fontSize: theme('fontSize.xs'),
                    fontWeight: theme('fontWeight.bold'),
                    color: theme('colors.gray.900'),
                    textTransform: 'uppercase',
                },
                '& .separator': {
                    width: '1px',
                    flex: '1',
                    backgroundColor: 'rgba(17, 24, 39, 0.1)',
                },
            },
            '& .card-image': {
                display: 'none',
                '@media (min-width: 640px)': {
                    display: 'block',
                    flexBasis: '14rem',
                },
                '& img': {
                    aspectRatio: '1',
                    height: '100%',
                    width: '100%',
                    objectFit: 'cover',
                },
            },
            '& .card-body': {
                display: 'flex',
                flex: '1',
                flexDirection: 'column',
                justifyContent: 'space-between',
            },
            '& .card-content': {
                borderLeft: '1px solid rgba(17, 24, 39, 0.1)',
                padding: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    borderLeft: 'transparent',
                    padding: theme('spacing.6'),
                },
            },
            '& h3': {
                fontWeight: theme('fontWeight.bold'),
                color: theme('colors.gray.900'),
                textTransform: 'uppercase',
            },
            '& p': {
                marginTop: theme('spacing.2'),
                display: '-webkit-box',
                '-webkit-line-clamp': '3',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                lineHeight: '1.625',
                color: theme('colors.gray.700'),
            },
            '& .card-footer': {
                '@media (min-width: 640px)': {
                    display: 'flex',
                    alignItems: 'flex-end',
                    justifyContent: 'flex-end',
                },
            },
            '& .card-cta': {
                display: 'block',
                backgroundColor: theme('colors.yellow.300'),
                paddingLeft: theme('spacing.5'),
                paddingRight: theme('spacing.5'),
                paddingTop: theme('spacing.3'),
                paddingBottom: theme('spacing.3'),
                textAlign: 'center',
                fontSize: theme('fontSize.xs'),
                fontWeight: theme('fontWeight.bold'),
                color: theme('colors.gray.900'),
                textTransform: 'uppercase',
                transition: 'background-color 0.3s',
                '&:hover': {
                    backgroundColor: theme('colors.yellow.400'),
                },
            },
        },
        '.card-overlay': {
            position: 'relative',
            overflow: 'hidden',
            borderRadius: theme('borderRadius.lg'),
            boxShadow: theme('boxShadow.sm'),
            transition: 'box-shadow 0.3s',
            '&:hover': {
                boxShadow: theme('boxShadow.lg'),
            },
            '& img': {
                position: 'absolute',
                inset: '0',
                height: '100%',
                width: '100%',
                objectFit: 'cover',
            },
            '& .card-overlay-content': {
                position: 'relative',
                background: 'linear-gradient(to top, rgba(17, 24, 39, 0.5), rgba(17, 24, 39, 0.25))',
                paddingTop: theme('spacing.32'),
                '@media (min-width: 640px)': {
                    paddingTop: theme('spacing.48'),
                },
                '@media (min-width: 1024px)': {
                    paddingTop: theme('spacing.64'),
                },
            },
            '& .card-content': {
                padding: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                },
            },
            '& time': {
                display: 'block',
                fontSize: theme('fontSize.xs'),
                color: 'rgba(255, 255, 255, 0.9)',
            },
            '& h3': {
                marginTop: theme('spacing.0.5'),
                fontSize: theme('fontSize.lg'),
                color: theme('colors.white'),
            },
            '& p': {
                marginTop: theme('spacing.2'),
                display: '-webkit-box',
                '-webkit-line-clamp': '3',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                lineHeight: '1.625',
                color: 'rgba(255, 255, 255, 0.95)',
            },
        },
        '.card-author': {
            display: 'block',
            borderRadius: theme('borderRadius.md'),
            border: `1px solid ${theme('colors.gray.300')}`,
            padding: theme('spacing.4'),
            boxShadow: theme('boxShadow.sm'),
            '@media (min-width: 640px)': {
                padding: theme('spacing.6'),
            },
            '& .card-header': {
                '@media (min-width: 640px)': {
                    display: 'flex',
                    justifyContent: 'space-between',
                    gap: theme('spacing.4'),
                },
                '@media (min-width: 1024px)': {
                    gap: theme('spacing.6'),
                },
            },
            '& .card-avatar': {
                '@media (min-width: 640px)': {
                    order: '2',
                    flexShrink: '0',
                },
                '& img': {
                    width: theme('spacing.16'),
                    height: theme('spacing.16'),
                    borderRadius: theme('borderRadius.full'),
                    objectFit: 'cover',
                    '@media (min-width: 640px)': {
                        width: theme('spacing.18'),
                        height: theme('spacing.18'),
                    },
                },
            },
            '& .card-info': {
                marginTop: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    marginTop: '0',
                },
            },
            '& h3': {
                fontSize: theme('fontSize.lg'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.gray.900'),
            },
            '& .author-name': {
                marginTop: theme('spacing.1'),
                fontSize: theme('fontSize.sm'),
                color: theme('colors.gray.700'),
            },
            '& .card-description': {
                marginTop: theme('spacing.4'),
                display: '-webkit-box',
                '-webkit-line-clamp': '2',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                color: theme('colors.gray.700'),
            },
            '& .card-meta': {
                marginTop: theme('spacing.6'),
                display: 'flex',
                gap: theme('spacing.4'),
                '@media (min-width: 1024px)': {
                    gap: theme('spacing.6'),
                },
            },
            '& .meta-item': {
                display: 'flex',
                alignItems: 'center',
                gap: theme('spacing.2'),
                '& svg': {
                    width: theme('spacing.5'),
                    height: theme('spacing.5'),
                },
                '& dd': {
                    fontSize: theme('fontSize.xs'),
                    color: theme('colors.gray.700'),
                },
            },
        },
        '.card-profile': {
            position: 'relative',
            display: 'block',
            backgroundColor: theme('colors.black'),
            '& img': {
                position: 'absolute',
                inset: '0',
                height: '100%',
                width: '100%',
                objectFit: 'cover',
                opacity: '0.75',
                transition: 'opacity 0.3s',
            },
            '&:hover img': {
                opacity: '0.5',
            },
            '& .card-content': {
                position: 'relative',
                padding: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                },
                '@media (min-width: 1024px)': {
                    padding: theme('spacing.8'),
                },
            },
            '& .card-role': {
                fontSize: theme('fontSize.sm'),
                fontWeight: theme('fontWeight.medium'),
                letterSpacing: theme('letterSpacing.widest'),
                color: theme('colors.pink.500'),
                textTransform: 'uppercase',
            },
            '& .card-name': {
                fontSize: theme('fontSize.xl'),
                fontWeight: theme('fontWeight.bold'),
                color: theme('colors.white'),
                '@media (min-width: 640px)': {
                    fontSize: theme('fontSize.2xl'),
                },
            },
            '& .card-description-wrapper': {
                marginTop: theme('spacing.32'),
                '@media (min-width: 640px)': {
                    marginTop: theme('spacing.48'),
                },
                '@media (min-width: 1024px)': {
                    marginTop: theme('spacing.64'),
                },
            },
            '& .card-description': {
                transform: 'translateY(2rem)',
                opacity: '0',
                transition: 'transform 0.3s, opacity 0.3s',
                fontSize: theme('fontSize.sm'),
                color: theme('colors.white'),
            },
            '&:hover .card-description': {
                transform: 'translateY(0)',
                opacity: '1',
            },
        },
        '.card-brutalist': {
            position: 'relative',
            display: 'block',
            height: theme('spacing.64'),
            '@media (min-width: 640px)': {
                height: theme('spacing.80'),
            },
            '@media (min-width: 1024px)': {
                height: theme('spacing.96'),
            },
            '& .card-border': {
                position: 'absolute',
                inset: '0',
                border: `2px dashed ${theme('colors.black')}`,
            },
            '& .card-inner': {
                position: 'relative',
                display: 'flex',
                height: '100%',
                transform: 'translateX(0) translateY(0)',
                alignItems: 'flex-end',
                border: `2px solid ${theme('colors.black')}`,
                backgroundColor: theme('colors.white'),
                transition: 'transform 0.3s',
            },
            '&:hover .card-inner': {
                transform: 'translateX(-0.5rem) translateY(-0.5rem)',
            },
            '& .card-front': {
                padding: theme('spacing.4'),
                paddingBottom: theme('spacing.4'),
                transition: 'opacity 0.3s',
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                    paddingBottom: theme('spacing.4'),
                },
                '@media (min-width: 1024px)': {
                    padding: theme('spacing.8'),
                    paddingBottom: theme('spacing.8'),
                },
            },
            '&:hover .card-front': {
                position: 'absolute',
                opacity: '0',
            },
            '& .card-front svg': {
                width: theme('spacing.10'),
                height: theme('spacing.10'),
                '@media (min-width: 640px)': {
                    width: theme('spacing.12'),
                    height: theme('spacing.12'),
                },
            },
            '& .card-front h2': {
                marginTop: theme('spacing.4'),
                fontSize: theme('fontSize.xl'),
                fontWeight: theme('fontWeight.medium'),
                '@media (min-width: 640px)': {
                    fontSize: theme('fontSize.2xl'),
                },
            },
            '& .card-back': {
                position: 'absolute',
                padding: theme('spacing.4'),
                opacity: '0',
                transition: 'opacity 0.3s',
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                },
                '@media (min-width: 1024px)': {
                    padding: theme('spacing.8'),
                },
            },
            '&:hover .card-back': {
                position: 'relative',
                opacity: '1',
            },
            '& .card-back h3': {
                marginTop: theme('spacing.4'),
                fontSize: theme('fontSize.xl'),
                fontWeight: theme('fontWeight.medium'),
                '@media (min-width: 640px)': {
                    fontSize: theme('fontSize.2xl'),
                },
            },
            '& .card-back p': {
                marginTop: theme('spacing.4'),
                fontSize: theme('fontSize.sm'),
                '@media (min-width: 640px)': {
                    fontSize: theme('fontSize.base'),
                },
            },
            '& .card-back .read-more': {
                marginTop: theme('spacing.8'),
                fontWeight: theme('fontWeight.bold'),
            },
        },
        '.card-property': {
            display: 'block',
            borderRadius: theme('borderRadius.lg'),
            padding: theme('spacing.4'),
            boxShadow: '0 1px 2px 0 rgba(99, 102, 241, 0.1)',
            '& img': {
                height: theme('spacing.56'),
                width: '100%',
                borderRadius: theme('borderRadius.md'),
                objectFit: 'cover',
            },
            '& .card-info': {
                marginTop: theme('spacing.2'),
            },
            '& .price': {
                fontSize: theme('fontSize.sm'),
                color: theme('colors.gray.500'),
            },
            '& .address': {
                fontWeight: theme('fontWeight.medium'),
            },
            '& .card-features': {
                marginTop: theme('spacing.6'),
                display: 'flex',
                alignItems: 'center',
                gap: theme('spacing.8'),
                fontSize: theme('fontSize.xs'),
            },
            '& .feature': {
                '@media (min-width: 640px)': {
                    display: 'inline-flex',
                    flexShrink: '0',
                    alignItems: 'center',
                    gap: theme('spacing.2'),
                },
                '& svg': {
                    width: theme('spacing.4'),
                    height: theme('spacing.4'),
                    color: theme('colors.indigo.700'),
                },
                '& .feature-content': {
                    marginTop: theme('spacing.1.5'),
                    '@media (min-width: 640px)': {
                        marginTop: '0',
                    },
                },
                '& .feature-label': {
                    color: theme('colors.gray.500'),
                },
                '& .feature-value': {
                    fontWeight: theme('fontWeight.medium'),
                },
            },
        },
        '.card-podcast': {
            borderRadius: theme('borderRadius.xl'),
            backgroundColor: theme('colors.white'),
            padding: theme('spacing.4'),
            boxShadow: `0 0 0 3px ${theme('colors.indigo.50')}`,
            '@media (min-width: 640px)': {
                padding: theme('spacing.6'),
            },
            '@media (min-width: 1024px)': {
                padding: theme('spacing.8'),
            },
            '& .card-inner': {
                display: 'flex',
                alignItems: 'flex-start',
                '@media (min-width: 640px)': {
                    gap: theme('spacing.8'),
                },
            },
            '& .card-visualizer': {
                display: 'none',
                '@media (min-width: 640px)': {
                    display: 'grid',
                    width: theme('spacing.20'),
                    height: theme('spacing.20'),
                    flexShrink: '0',
                    placeContent: 'center',
                    borderRadius: theme('borderRadius.full'),
                    border: `2px solid ${theme('colors.indigo.500')}`,
                },
            },
            '& .visualizer-bars': {
                display: 'flex',
                alignItems: 'center',
                gap: theme('spacing.1'),
                '& span': {
                    width: '0.125rem',
                    borderRadius: theme('borderRadius.full'),
                    backgroundColor: theme('colors.indigo.500'),
                    '&:nth-child(1)': { height: theme('spacing.8') },
                    '&:nth-child(2)': { height: theme('spacing.6') },
                    '&:nth-child(3)': { height: theme('spacing.4') },
                    '&:nth-child(4)': { height: theme('spacing.6') },
                    '&:nth-child(5)': { height: theme('spacing.8') },
                },
            },
            '& .episode-badge': {
                borderRadius: theme('borderRadius.sm'),
                border: `1px solid ${theme('colors.indigo.500')}`,
                backgroundColor: theme('colors.indigo.500'),
                paddingLeft: theme('spacing.3'),
                paddingRight: theme('spacing.3'),
                paddingTop: theme('spacing.1.5'),
                paddingBottom: theme('spacing.1.5'),
                fontSize: '10px',
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.white'),
            },
            '& h3': {
                marginTop: theme('spacing.4'),
                fontSize: theme('fontSize.lg'),
                fontWeight: theme('fontWeight.medium'),
                '@media (min-width: 640px)': {
                    fontSize: theme('fontSize.xl'),
                },
            },
            '& .card-description': {
                marginTop: theme('spacing.1'),
                fontSize: theme('fontSize.sm'),
                color: theme('colors.gray.700'),
            },
            '& .card-meta': {
                marginTop: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    display: 'flex',
                    alignItems: 'center',
                    gap: theme('spacing.2'),
                },
            },
            '& .duration': {
                display: 'flex',
                alignItems: 'center',
                gap: theme('spacing.1'),
                color: theme('colors.gray.500'),
                '& svg': {
                    width: theme('spacing.4'),
                    height: theme('spacing.4'),
                },
                '& p': {
                    fontSize: theme('fontSize.xs'),
                    fontWeight: theme('fontWeight.medium'),
                },
            },
            '& .separator': {
                display: 'none',
                '@media (min-width: 640px)': {
                    display: 'block',
                },
            },
            '& .featuring': {
                marginTop: theme('spacing.2'),
                fontSize: theme('fontSize.xs'),
                fontWeight: theme('fontWeight.medium'),
                color: theme('colors.gray.500'),
                '@media (min-width: 640px)': {
                    marginTop: '0',
                },
                '& a': {
                    textDecoration: 'underline',
                    '&:hover': {
                        color: theme('colors.gray.700'),
                    },
                },
            },
        },
        '.card-discussion': {
            borderRadius: theme('borderRadius.xl'),
            border: `2px solid ${theme('colors.gray.100')}`,
            backgroundColor: theme('colors.white'),
            '& .card-main': {
                display: 'flex',
                alignItems: 'flex-start',
                gap: theme('spacing.4'),
                padding: theme('spacing.4'),
                '@media (min-width: 640px)': {
                    padding: theme('spacing.6'),
                },
                '@media (min-width: 1024px)': {
                    padding: theme('spacing.8'),
                },
            },
            '& .card-avatar': {
                display: 'block',
                flexShrink: '0',
                '& img': {
                    width: theme('spacing.14'),
                    height: theme('spacing.14'),
                    borderRadius: theme('borderRadius.lg'),
                    objectFit: 'cover',
                },
            },
            '& h3': {
                fontWeight: theme('fontWeight.medium'),
                '@media (min-width: 640px)': {
                    fontSize: theme('fontSize.lg'),
                },
                '& a:hover': {
                    textDecoration: 'underline',
                },
            },
            '& .card-description': {
                display: '-webkit-box',
                '-webkit-line-clamp': '2',
                '-webkit-box-orient': 'vertical',
                overflow: 'hidden',
                fontSize: theme('fontSize.sm'),
                color: theme('colors.gray.700'),
            },
            '& .card-meta': {
                marginTop: theme('spacing.2'),
                '@media (min-width: 640px)': {
                    display: 'flex',
                    alignItems: 'center',
                    gap: theme('spacing.2'),
                },
            },
            '& .comments': {
                display: 'flex',
                alignItems: 'center',
                gap: theme('spacing.1'),
                color: theme('colors.gray.500'),
                '& svg': {
                    width: theme('spacing.4'),
                    height: theme('spacing.4'),
                },
                '& p': {
                    fontSize: theme('fontSize.xs'),
                },
            },
            '& .separator': {
                display: 'none',
                '@media (min-width: 640px)': {
                    display: 'block',
                },
            },
            '& .author-info': {
                display: 'none',
                '@media (min-width: 640px)': {
                    display: 'block',
                    fontSize: theme('fontSize.xs'),
                    color: theme('colors.gray.500'),
                },
                '& a': {
                    fontWeight: theme('fontWeight.medium'),
                    textDecoration: 'underline',
                    '&:hover': {
                        color: theme('colors.gray.700'),
                    },
                },
            },
            '& .card-footer': {
                display: 'flex',
                justifyContent: 'flex-end',
            },
            '& .solved-badge': {
                marginRight: '-0.125rem',
                marginBottom: '-0.125rem',
                display: 'inline-flex',
                alignItems: 'center',
                gap: theme('spacing.1'),
                borderTopLeftRadius: theme('borderRadius.xl'),
                borderBottomRightRadius: theme('borderRadius.xl'),
                backgroundColor: theme('colors.green.600'),
                paddingLeft: theme('spacing.3'),
                paddingRight: theme('spacing.3'),
                paddingTop: theme('spacing.1.5'),
                paddingBottom: theme('spacing.1.5'),
                color: theme('colors.white'),
                '& svg': {
                    width: theme('spacing.4'),
                    height: theme('spacing.4'),
                },
                '& span': {
                    fontSize: '10px',
                    fontWeight: theme('fontWeight.medium'),
                    '@media (min-width: 640px)': {
                        fontSize: theme('fontSize.xs'),
                    },
                },
            },
        },
    };

    addComponents(cards);
};

export default tailwindCardsPlugin;
