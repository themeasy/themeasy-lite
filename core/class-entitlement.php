<?php
/**
 * Entitlement facade — single source of entitlement truth.
 *
 * The whole plugin asks this class "what is this account allowed to do?" and
 * never reads the licensing source directly. Backed solely by the embedded
 * Freemius SDK (`themeasy_fs()`), which is the only runtime licensing authority
 * — the transitional legacy-theme fallback has been removed.
 *
 * This is the ONLY file that may reference `themeasy_fs()` — route every
 * entitlement question through here so the source stays swappable.
 *
 * Two paid offers (backlog #262):
 *  - can_use_widgets(): the widget layer — the Pro widgets of the content
 *    categories, their assets and Themeasy Motion, on any theme. Any paid plan
 *    or trial (the Widgets plan and up), or a licensed premium theme (M2).
 *  - can_use_premium(): the full offer — everything the widget layer gets, plus
 *    the site features (settings panel, theme builder, Global Sections, the
 *    contextual widget categories, WooCommerce/CF7). Pro/Agency, or M2.
 * can_use_premium() implies can_use_widgets(); never the other way round.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

namespace Themeasy\Core;

defined( 'ABSPATH' ) || exit;

final class Entitlement {
  /**
   * Memoized active-entitlement state for the current request.
   *
   * @var bool|null
   */
  private static ?bool $is_active = null;

  /**
   * Memoized plan slug for the current request.
   *
   * @var string|null
   */
  private static ?string $plan = null;

  /**
   * Raw Freemius instance accessor — the ONLY place themeasy_fs() is referenced.
   *
   * Guarded with function_exists() so the facade degrades gracefully (returns
   * null) if the SDK is ever unavailable, instead of fatally erroring.
   *
   * @return \Freemius|null
   */
  private static function fs(): ?\Freemius {
    return function_exists( 'themeasy_fs' ) ? \themeasy_fs() : null;
  }

  /**
   * Whether any active premium entitlement is present.
   *
   * SDK-backed: true when the embedded Freemius SDK reports usable premium code
   * (paid or trial). The transitional legacy-theme fallback was removed in Phase
   * 4, so the SDK is the sole source.
   *
   * @return bool
   */
  public static function is_active(): bool {
    if ( null === self::$is_active ) {
      $fs = self::fs();

      self::$is_active = $fs && $fs->can_use_premium_code();
    }

    return self::$is_active;
  }

  /**
   * Widget-layer gate: any paid plan or trial, OR a licensed premium theme.
   *
   * The Pro widgets of the content categories, their assets (GSAP, the core JS
   * runtime, the vendors) and Themeasy Motion — the whole Widgets plan, which
   * every higher offer includes. Not a site feature: nothing behind this gate
   * may restyle or extend the host theme.
   *
   * The paid branch reads "any license with features enabled, or a trial", not a
   * plan name: the SDK's is_plan() answers false when the asked name is missing
   * from the install's cached plan list, so asking for 'widgets' would drop the
   * widgets on an install that synced its plans before that plan existed.
   *
   * @return bool
   */
  public static function can_use_widgets(): bool {
    $fs = self::fs();

    if ( !$fs ) {
      return false;
    }

    // The plugin's own license/trial, any paid plan (the SDK method is
    // is_premium()-guarded, so it is false in the Free build).

    return self::has_premium_theme( $fs );
  }

  /**
   * Full-offer gate: SaaS Pro/Agency (or trial) OR a licensed premium theme.
   *
   * The site features on top of the widget layer: the settings panel, the theme
   * builder, Global Sections, the contextual widget categories, the
   * header/container tooling, WooCommerce and CF7. True when EITHER the plugin
   * holds its own Pro/Agency license or trial, OR a licensed premium Themeasy
   * theme is active on the site (M2, the ThemeForest model — the buyer holds a
   * THEME license, not a plugin license).
   *
   * The plan is read by exact name (see is_full_offer_plan()), so a Widgets
   * license or trial answers false whatever the plan order.
   *
   * @return bool
   */
  public static function can_use_premium(): bool {
    $fs = self::fs();

    if ( !$fs ) {
      return false;
    }

    $full = self::has_premium_theme( $fs );

    // The plugin's own Pro/Agency license or trial (the SDK method is
    // is_premium()-guarded, so this branch is false in the Free build).

    /**
     * Filters whether the full offer is on, downgrade-only.
     *
     * The filter can take the full offer away, never grant it: the site then
     * keeps the widget layer (can_use_widgets()) with no site feature. The bench
     * harnesses use it on top of M2 to emulate a Widgets-plan site.
     *
     * @param bool $enabled Whether to keep the full offer. Default true.
     */
    return $full && (bool) apply_filters( 'themeasy/entitlement/full_offer', true );
  }

  /**
   * Whether a licensed premium Themeasy theme is active on this site (M2).
   *
   * The theme exposes the signal via tbase/premium_theme/is_active. is_premium()
   * keeps the Free build from ever loading the (stripped) premium code through it.
   *
   * @param \Freemius $fs The plugin's SDK instance.
   * @return bool
   */
  private static function has_premium_theme( \Freemius $fs ): bool {
    return $fs->is_premium() && (bool) apply_filters( 'tbase/premium_theme/is_active', false );
  }

  /**
   * Whether the plugin's own license or trial is on a full-offer plan (Pro or
   * Agency).
   *
   * Exact plan names, never the SDK's "this plan or higher" ($exact = false):
   * that read compares positions in the install's cached plan list, and the SDK
   * appends a plan missing from the cache at the END of it (get_trial_plan(),
   * sync_plan_if_not_exist()), where it outranks Agency. On an install that
   * cached its plans before the Widgets plan existed, a Widgets trial or license
   * would then read as Pro — the full offer and the SaaS library (backlog #262).
   *
   * @param \Freemius $fs The plugin's SDK instance.
   * @return bool
   */
  private static function is_full_offer_plan( \Freemius $fs ): bool {
    return $fs->is_plan_or_trial( 'pro', true ) || $fs->is_plan_or_trial( 'agency', true );
  }

  /**
   * Narrow gate: a recurring SaaS subscription only (Pro/Agency, incl. trial).
   *
   * Guards SaaS-exclusive features (living/AI library, unlimited sites) that a
   * perpetual theme buyer must not get — hence it consults only the plugin's own
   * Freemius plan and deliberately ignores the premium-theme signal that
   * can_use_premium() honors. The Widgets plan is not a subscriber. Plans are
   * read by exact name (see is_full_offer_plan()).
   *
   * @return bool
   */
  public static function is_subscriber(): bool {
    $fs = self::fs();

    return $fs && self::is_full_offer_plan( $fs );
  }

  /**
   * Whether the SaaS Pro plan (or higher) is active, including trial.
   *
   * Pro or Agency by exact name (see is_full_offer_plan()); trial counts.
   *
   * @return bool
   */
  public static function is_pro(): bool {
    $fs = self::fs();

    return $fs && self::is_full_offer_plan( $fs );
  }

  /**
   * Whether the SaaS Agency plan is active (white-label + agency tools).
   *
   * 'agency' is the top plan, so the $exact flag is moot here (nothing is higher).
   *
   * @return bool
   */
  public static function is_agency(): bool {
    $fs = self::fs();

    return $fs && $fs->is_plan_or_trial( 'agency', true );
  }

  /**
   * The active plan slug.
   *
   * Derived from the SDK plan name; 'free' when there is no active paid plan
   * (anonymous/unregistered). 'perpetual' (one-off theme buyers) is introduced in
   * a later phase when the theme-license signal is wired.
   *
   * @return string One of 'free', 'widgets', 'pro', 'agency'.
   */
  public static function plan(): string {
    if ( null === self::$plan ) {
      $fs = self::fs();
      $plan = $fs ? $fs->get_plan() : false;

      self::$plan = ( is_object( $plan ) && !empty( $plan->name ) ) ? $plan->name : 'free';
    }

    return self::$plan;
  }

  /**
   * Whether the site has no premium entitlement of any kind.
   *
   * Logical negation of the widget-layer gate, the lowest paid entitlement.
   *
   * @return bool
   */
  public static function is_free(): bool {
    return !self::can_use_widgets();
  }

  /**
   * Whether the plugin holds its OWN Freemius license on this install.
   *
   * A buyer of a plugin plan (Widgets, Pro, Agency) — as opposed to M2, where the
   * license belongs to the theme and is managed by the theme wizard and the Hub.
   * Only such an install has a license to manage in the SDK's Account page.
   *
   * @return bool
   */
  public static function has_own_license(): bool {
    $fs = self::fs();

    return $fs && null !== self::own_license( $fs );
  }

  /**
   * The SDK Account page URL of an install that holds its own license, or ''.
   *
   * Where a plugin-plan buyer manages the license (see has_own_license()).
   *
   * @return string
   */
  public static function own_license_account_url(): string {
    $fs = self::fs();

    return ( $fs && null !== self::own_license( $fs ) ) ? (string) $fs->get_account_url() : '';
  }

  /**
   * The Freemius license proof (id + secret key) for the Template Library gate.
   *
   * The backstage validates a Pro/Agency license server-to-server, so the
   * in-editor client must present the install's own license id + secret key
   * (carried in request headers, never the URL). Only a registered SaaS
   * subscriber holds one; a free site — or an M2 theme buyer, who holds a THEME
   * license, not a plugin one — returns an empty proof and can insert only `free`
   * templates. This keeps the living library SaaS-exclusive without a separate
   * check (anti-cannibalization).
   *
   * A Widgets-plan license is a real paid license too, and the backstage accepts
   * any active paid license unless its plan allowlist is set, so the proof is
   * handed over only to a subscriber — the same gate as the panel's lock state
   * (backlog #262).
   *
   * The raw license entity is read only through own_license() — keep it inside
   * this facade so the themeasy_fs() instance stays encapsulated.
   *
   * @return array{id:int,key:string}
   */
  public static function library_license_proof(): array {
    $fs = self::fs();

    if ( !$fs || !self::is_subscriber() ) {
      return ['id' => 0, 'key' => ''];
    }

    $license = self::own_license( $fs );

    if ( null === $license || empty( $license->id ) || empty( $license->secret_key ) ) {
      return ['id' => 0, 'key' => ''];
    }

    return ['id' => (int) $license->id, 'key' => (string) $license->secret_key];
  }

  /**
   * The install's own license entity, or null.
   *
   * _get_license() is a Freemius SDK internal (leading underscore = unstable
   * API). This is the sole call site of it, for the raw object carrying the
   * secret key — isolating it here keeps the blast radius minimal.
   *
   * @param \Freemius $fs The plugin's SDK instance.
   * @return object|null
   */
  private static function own_license( \Freemius $fs ): ?object {
    if ( !$fs->is_registered() ) {
      return null;
    }

    $license = $fs->_get_license();

    return is_object( $license ) ? $license : null;
  }
}
